<?php

namespace App\Tests\Twig;

use App\Twig\JwtExpiryExtension;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class JwtExpiryExtensionTest extends TestCase
{
    public function testReturnsExpiryTimestampFromJwtCookie(): void
    {
        $request = Request::create('/');
        $request->cookies->set('BEARER', 'signed.jwt');

        $jwtManager = $this->createMock(JWTTokenManagerInterface::class);
        $jwtManager->expects(self::once())
            ->method('parse')
            ->with('signed.jwt')
            ->willReturn(['exp' => 1_700_000_000, 'username' => 'test@example.com']);

        self::assertSame(1_700_000_000, $this->createExtension($request, $jwtManager)->getJwtExpiresAt());
    }

    public function testReturnsNullWhenThereIsNoCookie(): void
    {
        $request = Request::create('/');

        $jwtManager = $this->createMock(JWTTokenManagerInterface::class);
        $jwtManager->expects(self::never())->method('parse');

        self::assertNull($this->createExtension($request, $jwtManager)->getJwtExpiresAt());
    }

    public function testReturnsNullWhenTokenIsInvalidOrExpired(): void
    {
        $request = Request::create('/');
        $request->cookies->set('BEARER', 'bad.jwt');

        $jwtManager = $this->createStub(JWTTokenManagerInterface::class);
        $jwtManager->method('parse')
            ->willThrowException(new JWTDecodeFailureException(JWTDecodeFailureException::EXPIRED_TOKEN, 'Expired JWT Token'));

        self::assertNull($this->createExtension($request, $jwtManager)->getJwtExpiresAt());
    }

    public function testReturnsNullWhenPayloadHasNoExpiry(): void
    {
        $request = Request::create('/');
        $request->cookies->set('BEARER', 'signed.jwt');

        $jwtManager = $this->createStub(JWTTokenManagerInterface::class);
        $jwtManager->method('parse')->willReturn(['username' => 'test@example.com']);

        self::assertNull($this->createExtension($request, $jwtManager)->getJwtExpiresAt());
    }

    private function createExtension(Request $request, JWTTokenManagerInterface $jwtManager): JwtExpiryExtension
    {
        $requestStack = new RequestStack();
        $requestStack->push($request);

        return new JwtExpiryExtension($requestStack, $jwtManager, 'BEARER');
    }
}
