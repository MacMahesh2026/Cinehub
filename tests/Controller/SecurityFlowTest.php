<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

final class SecurityFlowTest extends WebTestCase
{
    public function testLoginPageIsPublic(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Please sign in');
    }

    public function testLoginPageShowsSessionExpiredNotice(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login?expired=1');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.alert', 'Your session has expired');
    }

    public function testApiRequiresJwt(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/me');

        self::assertResponseStatusCodeSame(401);
        self::assertResponseHeaderSame('Content-Type', 'application/json');
    }

    public function testProtectedPageRedirectsToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/my-bookings');

        self::assertResponseRedirects('/login');
    }

    public function testInvalidJwtCookieRedirectsToLoginAndClearsCookie(): void
    {
        $client = static::createClient();
        $client->getCookieJar()->set(new Cookie('BEARER', 'not-a-jwt'));

        $client->request('GET', '/');

        self::assertResponseRedirects('/login?expired=1');

        $cookie = $client->getResponse()->headers->getCookies()[0] ?? null;
        self::assertNotNull($cookie);
        self::assertSame('BEARER', $cookie->getName());
    }

    public function testLogoutClearsCookie(): void
    {
        $client = static::createClient();
        $client->request('GET', '/logout');

        self::assertResponseRedirects('/');

        $cookie = $client->getResponse()->headers->getCookies()[0] ?? null;
        self::assertNotNull($cookie);
        self::assertSame('BEARER', $cookie->getName());
    }
}
