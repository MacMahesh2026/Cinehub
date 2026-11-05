<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * Sends API callers a 401 JSON response and browser visitors to the login page
 * when a protected route is accessed without a valid JWT.
 */
final class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        if (str_starts_with($request->getPathInfo(), '/api/')) {
            return new JsonResponse(
                ['code' => Response::HTTP_UNAUTHORIZED, 'message' => 'Authentication required.'],
                Response::HTTP_UNAUTHORIZED,
            );
        }

        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }
}
