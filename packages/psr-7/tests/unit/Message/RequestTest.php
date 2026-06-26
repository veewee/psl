<?php

declare(strict_types=1);

namespace Psl\Psr\Http\Tests\Unit\Message;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psl\IO\MemoryHandle;
use Psl\Psr\Http\Message\Request;
use Psl\Psr\Http\Message\Stream;
use Psl\Psr\Http\Message\Uri;
use Psr\Http\Message\StreamInterface;

use function array_filter;
use function array_keys;
use function array_values;
use function strtolower;

final class RequestTest extends TestCase
{
    // -----------------------------------------------------------------------
    // Construction basics
    // -----------------------------------------------------------------------

    public function testDefaultProtocolVersion(): void
    {
        $req = new Request('GET', Uri::fromString('http://example.com/'));

        static::assertSame('1.1', $req->getProtocolVersion());
    }

    public function testDefaultBodyIsEmptyReadableStream(): void
    {
        $req = new Request('GET', Uri::fromString('http://example.com/'));
        $body = $req->getBody();

        static::assertInstanceOf(StreamInterface::class, $body);
        static::assertTrue($body->isReadable());
        static::assertSame('', $body->getContents());
    }

    public function testGetMethod(): void
    {
        $req = new Request('POST', Uri::fromString('http://example.com/'));

        static::assertSame('POST', $req->getMethod());
    }

    public function testGetUri(): void
    {
        $uri = Uri::fromString('http://example.com/path');
        $req = new Request('GET', $uri);

        static::assertSame($uri, $req->getUri());
    }

    // -----------------------------------------------------------------------
    // Host header derived from URI
    // -----------------------------------------------------------------------

    public function testConstructorSetsHostFromUri(): void
    {
        $req = new Request('GET', Uri::fromString('http://example.com/'));

        static::assertSame('example.com', $req->getHeaderLine('Host'));
    }

    public function testConstructorSetsHostWithNonStandardPort(): void
    {
        $req = new Request('GET', Uri::fromString('http://example.com:8080/'));

        static::assertSame('example.com:8080', $req->getHeaderLine('Host'));
    }

    public function testConstructorSetsHostWithStandardPortSuppressed(): void
    {
        $req = new Request('GET', Uri::fromString('http://example.com:80/'));

        // Standard port 80 for http is suppressed, so no port in Host
        static::assertSame('example.com', $req->getHeaderLine('Host'));
    }

    // -----------------------------------------------------------------------
    // getRequestTarget
    // -----------------------------------------------------------------------

    public function testGetRequestTargetDefaultsToSlashWhenPathEmpty(): void
    {
        $req = new Request('GET', Uri::fromString('http://example.com'));

        static::assertSame('/', $req->getRequestTarget());
    }

    public function testGetRequestTargetFromPath(): void
    {
        $req = new Request('GET', Uri::fromString('http://example.com/foo/bar'));

        static::assertSame('/foo/bar', $req->getRequestTarget());
    }

    public function testGetRequestTargetIncludesQuery(): void
    {
        $req = new Request('GET', Uri::fromString('http://example.com/foo?x=1&y=2'));

        static::assertSame('/foo?x=1&y=2', $req->getRequestTarget());
    }

    // -----------------------------------------------------------------------
    // withRequestTarget
    // -----------------------------------------------------------------------

    public function testWithRequestTargetStoresExplicitTarget(): void
    {
        $req = new Request('GET', Uri::fromString('http://example.com/'));
        $new = $req->withRequestTarget('/custom-target');

        static::assertSame('/custom-target', $new->getRequestTarget());
    }

    public function testWithRequestTargetIsImmutable(): void
    {
        $req = new Request('GET', Uri::fromString('http://example.com/'));
        $new = $req->withRequestTarget('/custom');

        static::assertNotSame($req, $new);
        static::assertSame('/', $req->getRequestTarget());
    }

    // -----------------------------------------------------------------------
    // withMethod
    // -----------------------------------------------------------------------

    public function testWithMethodReturnsNewInstance(): void
    {
        $req = new Request('GET', Uri::fromString('http://example.com/'));
        $new = $req->withMethod('DELETE');

        static::assertNotSame($req, $new);
        static::assertSame('GET', $req->getMethod());
        static::assertSame('DELETE', $new->getMethod());
    }

    // -----------------------------------------------------------------------
    // withUri
    // -----------------------------------------------------------------------

    public function testWithUriUpdatesHostHeader(): void
    {
        $req = new Request('GET', Uri::fromString('http://old.com/'));
        $new = $req->withUri(Uri::fromString('http://new.com/path'));

        static::assertSame('new.com', $new->getHeaderLine('Host'));
    }

    public function testWithUriPreserveHostKeepsExistingHost(): void
    {
        $req = new Request('GET', Uri::fromString('http://old.com/'));
        $new = $req->withUri(Uri::fromString('http://new.com/path'), true);

        static::assertSame('old.com', $new->getHeaderLine('Host'));
    }

    public function testWithUriPreserveHostFalseWhenNoExistingHostSetsNewHost(): void
    {
        // Even with preserveHost=true, if there is no existing host header, we update
        $uri = Uri::fromString('');
        /** @var Request $req */
        $req = (new Request('GET', $uri))->withoutHeader('Host');
        $new = $req->withUri(Uri::fromString('http://new.com/'), true);

        static::assertSame('new.com', $new->getHeaderLine('Host'));
    }

    public function testWithUriIsImmutable(): void
    {
        $req = new Request('GET', Uri::fromString('http://old.com/'));
        $new = $req->withUri(Uri::fromString('http://new.com/'));

        static::assertNotSame($req, $new);
        static::assertSame('old.com', $req->getHeaderLine('Host'));
    }

    // -----------------------------------------------------------------------
    // Headers (via MessageTrait)
    // -----------------------------------------------------------------------

    public function testHeaderCaseInsensitiveRead(): void
    {
        $req = new Request('GET', Uri::fromString('http://example.com/'));
        $req = $req->withHeader('Content-Type', 'application/json');

        static::assertSame(['application/json'], $req->getHeader('content-type'));
        static::assertSame(['application/json'], $req->getHeader('CONTENT-TYPE'));
    }

    public function testGetHeadersPreservesOriginalCase(): void
    {
        $req = new Request('GET', Uri::fromString('http://example.com/'));
        $req = $req->withHeader('Content-Type', 'application/json');
        $headers = $req->getHeaders();

        static::assertArrayHasKey('Content-Type', $headers);
    }

    public function testGetHeaderLineJoinsMultipleValues(): void
    {
        $req = new Request('GET', Uri::fromString('http://example.com/'));
        $req = $req->withHeader('Accept', 'text/html');
        $req = $req->withAddedHeader('Accept', 'application/json');

        static::assertSame('text/html, application/json', $req->getHeaderLine('Accept'));
    }

    public function testWithAddedHeaderAppends(): void
    {
        $req = new Request('GET', Uri::fromString('http://example.com/'));
        $req = $req->withHeader('X-Foo', 'first');
        $req = $req->withAddedHeader('X-Foo', 'second');

        static::assertSame(['first', 'second'], $req->getHeader('X-Foo'));
    }

    public function testWithoutHeaderRemoves(): void
    {
        $req = new Request('GET', Uri::fromString('http://example.com/'));
        $req = $req->withHeader('X-Foo', 'bar');
        $req = $req->withoutHeader('x-foo');

        static::assertFalse($req->hasHeader('X-Foo'));
    }

    public function testInvalidHeaderNameThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $req = new Request('GET', Uri::fromString('http://example.com/'));
        $req->withHeader('a b', 'value');
    }

    public function testInvalidHeaderValueThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $req = new Request('GET', Uri::fromString('http://example.com/'));
        $req->withHeader('X-Foo', "\x00bad");
    }

    // -----------------------------------------------------------------------
    // withProtocolVersion
    // -----------------------------------------------------------------------

    public function testWithProtocolVersionReturnsNewInstance(): void
    {
        $req = new Request('GET', Uri::fromString('http://example.com/'));
        $new = $req->withProtocolVersion('2.0');

        static::assertNotSame($req, $new);
        static::assertSame('1.1', $req->getProtocolVersion());
        static::assertSame('2.0', $new->getProtocolVersion());
    }

    // -----------------------------------------------------------------------
    // withBody
    // -----------------------------------------------------------------------

    public function testWithBodyReturnsNewInstance(): void
    {
        $req = new Request('GET', Uri::fromString('http://example.com/'));
        $stream = Stream::fromHandle(new MemoryHandle('hello'));
        $new = $req->withBody($stream);

        static::assertNotSame($req, $new);
        static::assertSame($stream, $new->getBody());
    }

    // -----------------------------------------------------------------------
    // HTTP method validation (Fix 1)
    // -----------------------------------------------------------------------

    public function testConstructRejectsEmptyMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Request('', Uri::fromString('http://example.com/'));
    }

    public function testConstructRejectsMethodWithSpace(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Request('GET POST', Uri::fromString('http://example.com/'));
    }

    public function testConstructRejectsMethodWithControlChar(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Request("G\nET", Uri::fromString('http://example.com/'));
    }

    public function testWithMethodRejectsEmptyMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $req = new Request('GET', Uri::fromString('http://example.com/'));
        $req->withMethod('');
    }

    public function testWithMethodRejectsMethodWithSpace(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $req = new Request('GET', Uri::fromString('http://example.com/'));
        $req->withMethod('GET POST');
    }

    public function testWithMethodRejectsMethodWithControlChar(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $req = new Request('GET', Uri::fromString('http://example.com/'));
        $req->withMethod("G\nET");
    }

    public function testValidMethodsAreAccepted(): void
    {
        $req = new Request('GET', Uri::fromString('http://example.com/'));
        static::assertSame('GET', $req->getMethod());

        $new = $req->withMethod('POST');
        static::assertSame('POST', $new->getMethod());

        $new2 = $req->withMethod('PATCH');
        static::assertSame('PATCH', $new2->getMethod());
    }

    // -----------------------------------------------------------------------
    // getHeaders casing grouping (Fix 2)
    // -----------------------------------------------------------------------

    public function testGetHeadersGroupsByFirstSeenCasing(): void
    {
        $req = new Request('GET', Uri::fromString('http://example.com/'));
        $req = $req->withHeader('Content-Type', 'a');
        $req = $req->withAddedHeader('content-type', 'b');

        $headers = $req->getHeaders();

        // Must have exactly one key for this header (case-insensitive)
        $contentTypeKeys = array_filter(array_keys($headers), static fn(string $k): bool => strtolower($k) === 'content-type');
        static::assertCount(1, $contentTypeKeys, 'getHeaders() must group case-insensitive header names under one key');

        $key = array_values($contentTypeKeys)[0];
        static::assertSame('Content-Type', $key, 'First-seen casing must be preserved as the map key');
        static::assertSame(['a', 'b'], $headers[$key], 'Both values must appear under the single key');
    }

    // -----------------------------------------------------------------------
    // withHeader array of values
    // -----------------------------------------------------------------------

    public function testWithHeaderAcceptsArrayOfValues(): void
    {
        $req = new Request('GET', Uri::fromString('http://example.com/'));
        $new = $req->withHeader('X-Foo', ['a', 'b']);

        static::assertSame(['a', 'b'], $new->getHeader('X-Foo'));
        static::assertSame('a, b', $new->getHeaderLine('X-Foo'));
    }

    public function testWithAddedHeaderAcceptsArrayOfValues(): void
    {
        $req = new Request('GET', Uri::fromString('http://example.com/'));
        $req = $req->withHeader('X-Foo', 'first');
        $new = $req->withAddedHeader('X-Foo', ['second', 'third']);

        static::assertSame(['first', 'second', 'third'], $new->getHeader('X-Foo'));
        static::assertSame('first, second, third', $new->getHeaderLine('X-Foo'));
    }
}
