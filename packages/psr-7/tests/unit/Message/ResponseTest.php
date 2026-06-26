<?php

declare(strict_types=1);

namespace Psl\Psr\Http\Tests\Unit\Message;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psl\Psr\Http\Message\Response;
use Psr\Http\Message\StreamInterface;

final class ResponseTest extends TestCase
{
    // -----------------------------------------------------------------------
    // Construction defaults
    // -----------------------------------------------------------------------

    public function testDefaultStatusCode(): void
    {
        $res = new Response();

        static::assertSame(200, $res->getStatusCode());
    }

    public function testDefaultReasonPhrase(): void
    {
        $res = new Response();

        static::assertSame('OK', $res->getReasonPhrase());
    }

    public function testDefaultProtocolVersion(): void
    {
        $res = new Response();

        static::assertSame('1.1', $res->getProtocolVersion());
    }

    public function testDefaultBodyIsEmptyReadableStream(): void
    {
        $res = new Response();
        $body = $res->getBody();

        static::assertInstanceOf(StreamInterface::class, $body);
        static::assertTrue($body->isReadable());
        static::assertSame('', $body->getContents());
    }

    // -----------------------------------------------------------------------
    // withStatus
    // -----------------------------------------------------------------------

    public function testWithStatus404SetsReasonPhraseViaReasonPhrase(): void
    {
        $res = new Response();
        $new = $res->withStatus(404);

        static::assertSame(404, $new->getStatusCode());
        static::assertSame('Not Found', $new->getReasonPhrase());
    }

    public function testWithStatus599IsValid(): void
    {
        $res = new Response();
        $new = $res->withStatus(599);

        static::assertSame(599, $new->getStatusCode());
    }

    public function testWithStatus100IsValid(): void
    {
        $res = new Response();
        $new = $res->withStatus(100);

        static::assertSame(100, $new->getStatusCode());
        static::assertSame('Continue', $new->getReasonPhrase());
    }

    public function testWithStatus99Throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Response())->withStatus(99);
    }

    public function testWithStatus600Throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Response())->withStatus(600);
    }

    public function testWithStatusCustomReasonPhraseRespected(): void
    {
        $res = new Response();
        $new = $res->withStatus(200, 'Everything Is Fine');

        static::assertSame('Everything Is Fine', $new->getReasonPhrase());
    }

    public function testWithStatusIsImmutable(): void
    {
        $res = new Response();
        $new = $res->withStatus(404);

        static::assertNotSame($res, $new);
        static::assertSame(200, $res->getStatusCode());
    }

    // -----------------------------------------------------------------------
    // Headers (MessageTrait via Response)
    // -----------------------------------------------------------------------

    public function testHeaderCaseInsensitiveRead(): void
    {
        $res = new Response();
        $res = $res->withHeader('Content-Type', 'application/json');

        static::assertSame(['application/json'], $res->getHeader('content-type'));
    }

    public function testGetHeadersPreservesOriginalCase(): void
    {
        $res = new Response();
        $res = $res->withHeader('Content-Length', '42');
        $headers = $res->getHeaders();

        static::assertArrayHasKey('Content-Length', $headers);
    }

    public function testGetHeaderLineJoinsValues(): void
    {
        $res = new Response();
        $res = $res->withHeader('Cache-Control', 'no-cache');
        $res = $res->withAddedHeader('Cache-Control', 'no-store');

        static::assertSame('no-cache, no-store', $res->getHeaderLine('Cache-Control'));
    }

    public function testWithoutHeaderRemovesHeader(): void
    {
        $res = new Response();
        $res = $res->withHeader('X-Custom', 'value');
        $res = $res->withoutHeader('x-custom');

        static::assertFalse($res->hasHeader('X-Custom'));
        static::assertSame([], $res->getHeader('X-Custom'));
    }

    public function testInvalidHeaderNameThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Response())->withHeader('bad name', 'value');
    }

    public function testInvalidHeaderValueThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Response())->withHeader('X-Custom', "\x7F");
    }

    // -----------------------------------------------------------------------
    // Immutability
    // -----------------------------------------------------------------------

    public function testWithHeaderIsImmutable(): void
    {
        $res = new Response();
        $new = $res->withHeader('X-Foo', 'bar');

        static::assertNotSame($res, $new);
        static::assertFalse($res->hasHeader('X-Foo'));
    }

    public function testWithProtocolVersionIsImmutable(): void
    {
        $res = new Response();
        $new = $res->withProtocolVersion('2.0');

        static::assertNotSame($res, $new);
        static::assertSame('1.1', $res->getProtocolVersion());
        static::assertSame('2.0', $new->getProtocolVersion());
    }
}
