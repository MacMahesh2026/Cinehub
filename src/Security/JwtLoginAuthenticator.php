<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

/**
 * Authenticates the Twig login form and issues a JWT stored in an HttpOnly
 * cookie. No server-side session is used to remember the user.
 */
final class JwtLoginAuthenticator extends AbstractLoginFormAuthenticator
{
    public function __construct(
        private UserRepository $users,
        private JWTTokenManagerInterface $jwtManager,
        private JwtCookieFactory $cookies,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate('app_login');
    }

    public function authenticate(Request $request): Passport
    {
        $email = strtolower(trim((string) $request->request->get('_username', '')));
        $password = (string) $request->request->get('_password', '');
        $csrfToken = (string) $request->request->get('_csrf_token', '');

        return new Passport(
            new UserBadge(
                $email,
                fn (string $identifier): ?User => $this->users->findOneBy(['email' => $identifier])
            ),
            new PasswordCredentials($password),
            [new CsrfTokenBadge('authenticate', $csrfToken)],
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return null;
        }

        $response = new RedirectResponse($this->urlGenerator->generate('home'));
        $response->headers->setCookie(
            $this->cookies->create($this->jwtManager->create($user), $request)
        );

        return $response;
    }
}
