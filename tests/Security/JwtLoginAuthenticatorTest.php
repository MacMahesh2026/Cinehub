<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\JwtCookieFactory;
use App\Security\JwtLoginAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

final class JwtLoginAuthenticatorTest extends TestCase
{
    public function testAuthenticateBuildsPassportFromFormCredentials(): void
    {
        $user = (new User())
            ->setName('Test User')
            ->setEmail('test@example.com')
            ->setPassword('hashed-password');

        $users = $this->createMock(UserRepository::class);
        $users->expects(self::once())
            ->method('findOneBy')
            ->with(['email' => 'test@example.com'])
            ->willReturn($user);

        $authenticator = $this->createAuthenticator($users);

        $request = Request::create('/login', 'POST', [
            '_username' => ' Test@Example.com ',
            '_password' => 'plain-password',
            '_csrf_token' => 'csrf-token',
        ]);

        $passport = $authenticator->authenticate($request);

        self::assertSame($user, $passport->getUser());
        self::assertSame('plain-password', $passport->getBadge(PasswordCredentials::class)->getPassword());
        self::assertTrue($passport->hasBadge(CsrfTokenBadge::class));
        self::assertTrue($passport->hasBadge(UserBadge::class));
    }

    public function testOnAuthenticationSuccessSetsJwtCookieAndRedirects(): void
    {
        $user = (new User())
            ->setName('Test User')
            ->setEmail('test@example.com')
            ->setPassword('hashed-password');

        $jwtManager = $this->createMock(JWTTokenManagerInterface::class);
        $jwtManager->expects(self::once())
            ->method('create')
            ->with($user)
            ->willReturn('signed.jwt.token');

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects(self::once())->method('generate')->with('home')->willReturn('/');

        $authenticator = new JwtLoginAuthenticator(
            $this->createStub(UserRepository::class),
            $jwtManager,
            new JwtCookieFactory('BEARER', 3600),
            $urlGenerator,
        );

        $request = Request::create('https://cinehub.test/login', 'POST');
        $token = new PostAuthenticationToken($user, 'web_login', $user->getRoles());

        $response = $authenticator->onAuthenticationSuccess($request, $token, 'web_login');

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/', $response->headers->get('Location'));

        $cookies = $response->headers->getCookies();
        self::assertCount(1, $cookies);
        self::assertSame('BEARER', $cookies[0]->getName());
        self::assertSame('signed.jwt.token', $cookies[0]->getValue());
    }

    private function createAuthenticator(UserRepository $users): JwtLoginAuthenticator
    {
        return new JwtLoginAuthenticator(
            $users,
            $this->createStub(JWTTokenManagerInterface::class),
            new JwtCookieFactory('BEARER', 120),
            $this->createStub(UrlGeneratorInterface::class),
        );
    }
}
