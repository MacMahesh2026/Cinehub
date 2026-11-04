<?php

namespace App\Security;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;

/**
 * Builds the HttpOnly cookie that carries the JWT for browser requests.
 *
 * The JWT is stored in a cookie (instead of localStorage) so normal Twig
 * navigation and form submissions are authenticated transparently, while the
 * API can still use the standard "Authorization: Bearer" header.
 */
final class JwtCookieFactory
{
    public function __construct(
        #[Autowire('%app.jwt_cookie_name%')]
        private string $cookieName,
        #[Autowire('%lexik_jwt_authentication.token_ttl%')]
        private int $ttl,
    ) {
    }

    public function create(string $jwt, Request $request): Cookie
    {
        return Cookie::create(
            $this->cookieName,
            $jwt,
            time() + $this->ttl,
            '/',
            null,
            $request->isSecure(),
            true,
            false,
            Cookie::SAMESITE_LAX,
        );
    }

    public function clear(): Cookie
    {
        return Cookie::create(
            $this->cookieName,
            null,
            1,
            '/',
            null,
            false,
            true,
            false,
            Cookie::SAMESITE_LAX,
        );
    }
}
