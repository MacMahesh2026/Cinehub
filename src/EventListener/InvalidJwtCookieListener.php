<?php

namespace App\EventListener;

use App\Security\JwtCookieFactory;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Clears an expired or invalid JWT cookie and sends browser visitors back to
 * the login page instead of showing a raw 401 JSON error.
 *
 * API requests are ignored so they keep receiving the normal 401 response.
 */
final class InvalidJwtCookieListener
{
    public function __construct(
        private JwtCookieFactory $cookies,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[AsEventListener(event: Events::JWT_EXPIRED)]
    #[AsEventListener(event: Events::JWT_INVALID)]
    public function onAuthenticationFailure(AuthenticationFailureEvent $event): void
    {
        $request = $event->getRequest();

        if ($request === null || str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        $response = new RedirectResponse($this->urlGenerator->generate('app_login', ['expired' => 1]));
        $response->headers->setCookie($this->cookies->clear());

        $event->setResponse($response);
    }
}
