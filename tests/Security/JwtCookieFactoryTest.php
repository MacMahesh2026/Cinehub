<?php

namespace App\Tests\Security;

use App\Security\JwtCookieFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class JwtCookieFactoryTest extends TestCase
{
    public function testCreateBuildsHttpOnlyCookie(): void
    {
        $factory = new JwtCookieFactory('BEARER', 3600);
        $cookie = $factory->create('the.jwt.token', Request::create('https://example.com/login'));

        self::assertSame('BEARER', $cookie->getName());
        self::assertSame('the.jwt.token', $cookie->getValue());
        self::assertTrue($cookie->isHttpOnly());
        self::assertTrue($cookie->isSecure());
        self::assertSame('/', $cookie->getPath());
        self::assertSame('lax', $cookie->getSameSite());
        self::assertGreaterThan(time(), $cookie->getExpiresTime());
    }

    public function testCreateCookieIsNotSecureOnPlainHttp(): void
    {
        $factory = new JwtCookieFactory('BEARER', 3600);
        $cookie = $factory->create('the.jwt.token', Request::create('http://localhost/login'));

        self::assertFalse($cookie->isSecure());
    }

    public function testClearExpiresCookie(): void
    {
        $factory = new JwtCookieFactory('BEARER', 3600);
        $cookie = $factory->clear();

        self::assertSame('BEARER', $cookie->getName());
        self::assertNull($cookie->getValue());
        self::assertLessThan(time(), $cookie->getExpiresTime());
    }
}
