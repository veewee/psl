<?php

declare(strict_types=1);

namespace Psl\Psr\Http\Tests\Unit\Factory;

use PHPUnit\Framework\TestCase;
use Psl\Psr\Http\Factory\RequestFactory;
use Psl\Psr\Http\Message\Uri;
use Psr\Http\Message\RequestInterface;

final class RequestFactoryTest extends TestCase
{
    public function testCreateRequestFromStringUri(): void
    {
        $factory = new RequestFactory();
        $request = $factory->createRequest('GET', 'http://example.com/path');

        static::assertInstanceOf(RequestInterface::class, $request);
        static::assertSame('GET', $request->getMethod());
        static::assertSame('example.com', $request->getHeaderLine('Host'));
        static::assertSame('/path', $request->getRequestTarget());
    }

    public function testCreateRequestFromUriInstance(): void
    {
        $factory = new RequestFactory();
        $uri = Uri::fromString('https://api.example.com/v1');
        $request = $factory->createRequest('POST', $uri);

        static::assertSame('POST', $request->getMethod());
        static::assertSame('api.example.com', $request->getHeaderLine('Host'));
    }

    public function testCreateRequestHasEmptyBody(): void
    {
        $factory = new RequestFactory();
        $request = $factory->createRequest('GET', 'http://example.com/');

        static::assertSame('', $request->getBody()->getContents());
    }

    public function testCreateRequestDefaultProtocolVersion(): void
    {
        $factory = new RequestFactory();
        $request = $factory->createRequest('GET', 'http://example.com/');

        static::assertSame('1.1', $request->getProtocolVersion());
    }
}
