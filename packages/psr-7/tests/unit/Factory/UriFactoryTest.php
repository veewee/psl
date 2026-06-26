<?php

declare(strict_types=1);

namespace Psl\Psr\Http\Tests\Unit\Factory;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psl\Psr\Http\Factory\UriFactory;
use Psl\Psr\Http\Message\Uri;

final class UriFactoryTest extends TestCase
{
    private UriFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new UriFactory();
    }

    public function testCreateUriParsesAbsoluteUrl(): void
    {
        $uri = $this->factory->createUri('https://example.com/path?q=1#frag');

        static::assertSame('https', $uri->getScheme());
        static::assertSame('example.com', $uri->getHost());
        static::assertSame('/path', $uri->getPath());
        static::assertSame('q=1', $uri->getQuery());
        static::assertSame('frag', $uri->getFragment());
    }

    public function testCreateUriEmptyStringReturnsEmptyUri(): void
    {
        $uri = $this->factory->createUri('');

        static::assertSame('', $uri->getScheme());
        static::assertSame('', $uri->getHost());
        static::assertSame('', $uri->getPath());
        static::assertSame('', $uri->getQuery());
        static::assertSame('', $uri->getFragment());
        static::assertSame('', (string) $uri);
    }

    public function testCreateUriReturnsUriInstance(): void
    {
        $uri = $this->factory->createUri('https://example.com/');

        static::assertInstanceOf(Uri::class, $uri);
    }

    public function testCreateUriMalformedThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // Double-slash with no host is technically valid, but an authority-only
        // form with empty host can be tricky. Use a clearly invalid input instead.
        $this->factory->createUri('http://[invalid-ipv6/path');
    }

    public function testCreateUriWithRelativePath(): void
    {
        $uri = $this->factory->createUri('/just/a/path');

        static::assertSame('', $uri->getScheme());
        static::assertSame('/just/a/path', $uri->getPath());
    }
}
