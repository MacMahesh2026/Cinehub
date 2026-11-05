<?php

namespace App\Twig;

use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Exposes the expiry timestamp of the JWT stored in the browser cookie so the
 * layout can schedule an automatic redirect to the login page.
 */
final class JwtExpiryExtension extends AbstractExtension
{
    public function __construct(
        private RequestStack $requestStack,
        private JWTTokenManagerInterface $jwtManager,
        #[Autowire('%app.jwt_cookie_name%')]
        private string $cookieName,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('jwt_expires_at', $this->getJwtExpiresAt(...)),
        ];
    }

    public function getJwtExpiresAt(): ?int
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return null;
        }

        $jwt = $request->cookies->get($this->cookieName);

        if (!is_string($jwt) || '' === $jwt) {
            return null;
        }

        try {
            $payload = $this->jwtManager->parse($jwt);
        } catch (JWTDecodeFailureException) {
            return null;
        }

        return isset($payload['exp']) ? (int) $payload['exp'] : null;
    }
}
