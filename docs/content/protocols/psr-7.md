# PSR-7

The `Psr\Http` component is a client-side PSR-7 / PSR-17 implementation built on PSL. It provides
the message types (`Uri`, `Request`, `Response`), a `Stream` that wraps any `Psl\IO` handle, and the
matching PSR-17 factories. Headers reuse `Psl\HTTP\Message\FieldMap`, URIs reuse `Psl\URI`, and stream
bodies stay on `Psl\IO`, so a PSR-7 stack can sit on top of PSL without copying whole bodies into memory.

Server-side types (`ServerRequest`, `UploadedFile`) are intentionally not part of this component. PSL's
own HTTP model is async and streaming rather than SAPI based, so server interop is better served by a
separate bridge.

## Stream

`Stream` wraps a `Psl\IO` handle as a PSR-7 `StreamInterface`. Its reported capabilities follow the
wrapped handle: `isReadable()`, `isWritable()`, and `isSeekable()` each check whether the handle
implements the matching `Psl\IO` interface.

    use Psl\IO\MemoryHandle;
    use Psl\Psr\Http\Message\Stream;

    $stream = Stream::fromHandle(new MemoryHandle('request body'), 12);

`getSize()` returns the byte count when it is known (a string length, a file size, or a spooled total)
and `null` otherwise, because a `Psl\IO` handle does not carry its own size. `detach()` returns `null`,
and `getMetadata()` returns `null` or an empty array, since a `Psl\IO` handle exposes no underlying
resource or metadata. `seek()` accepts `SEEK_END` only when the size is known.

## Spooled Streams

When you need a known `Content-Length` and the ability to rewind for retries without keeping the whole
body in memory, use `Stream::spooled()`. It drains the source handle into a `Psl\IO\spool()` buffer,
which holds data in memory up to a threshold and then writes to a temporary file on disk.

    use Psl\Psr\Http\Message\Stream;

    $stream = Stream::spooled($readHandle);
    // seekable, getSize() is known, content spills to disk past the threshold

## StreamReadHandle

`StreamReadHandle` is the reverse adapter. It wraps a PSR-7 `StreamInterface` as a
`Psl\IO\ReadHandleInterface`, so a PSL stream consumer can read a PSR-7 body. One use is feeding a
PSR-7 response body into `Psl\MIME\Parser`.

    use Psl\Psr\Http\Message\StreamReadHandle;

    $handle = new StreamReadHandle($response->getBody());

It is read-only. `read()` delegates to the PSR-7 stream and falls back to an 8192-byte chunk when no
size is given. `reachedEndOfDataSource()` maps to the stream's `eof()`.

## Uri

`Uri` implements `UriInterface` on top of `Psl\URI`, so parsing, normalization, and percent-encoding
follow RFC 3986. Scheme and host are lowercased, and `getPort()` returns `null` when the port is the
default for the scheme.

    use Psl\Psr\Http\Message\Uri;

    $uri = Uri::fromString('https://user@example.com/path?q=1#frag');
    $uri->getHost();      // 'example.com'
    $uri->getPort();      // null (443 is standard for https)
    $uri = $uri->withScheme('http')->withQuery('');

Every `withX` method returns a new instance. `withPort()` outside 0 to 65535 throws
`InvalidArgumentException`, as does malformed input passed to `Uri::fromString()`. An empty query or
fragment passed to `withQuery('')` / `withFragment('')` removes that component.

## Request and Response

`Request` and `Response` implement `RequestInterface` and `ResponseInterface`. Both are immutable: every
`withX` returns a new instance and leaves the original untouched. Headers are stored in
`Psl\HTTP\Message\FieldMap` (ordered and case-insensitive), and the body defaults to an empty `Stream`
so `getBody()` is never `null`.

    use Psl\Psr\Http\Message\Request;
    use Psl\Psr\Http\Message\Uri;

    $request = (new Request('GET', Uri::fromString('https://example.com/users')))
        ->withHeader('Accept', 'application/json');
    $request->getHeaderLine('accept');   // 'application/json' (case-insensitive lookup)

`Request` derives the `Host` header from the URI; `withUri($uri, preserveHost: true)` keeps an existing
`Host`. `Response` validates the status code to the 100 to 599 range and falls back to
`Psl\HTTP\Message\reason_phrase()` for the reason phrase when none is given.

    use Psl\Psr\Http\Message\Response;

    $response = (new Response())->withStatus(404);
    $response->getReasonPhrase();   // 'Not Found'

Header names and values are validated; an invalid name or value throws `InvalidArgumentException`.

## Factories

The PSR-17 factories are thin and construct the types above.

    use Psl\Psr\Http\Factory\RequestFactory;
    use Psl\Psr\Http\Factory\ResponseFactory;
    use Psl\Psr\Http\Factory\StreamFactory;
    use Psl\Psr\Http\Factory\UriFactory;

    $request  = (new RequestFactory())->createRequest('POST', 'https://example.com/upload');
    $response = (new ResponseFactory())->createResponse(201);
    $uri      = (new UriFactory())->createUri('https://example.com');

    $factory      = new StreamFactory();
    $fromString   = $factory->createStream('body');
    $fromFile     = $factory->createStreamFromFile('/path/to/file');
    $fromResource = $factory->createStreamFromResource($resource);

`RequestFactory::createRequest()` accepts a string or a `UriInterface`; a string is parsed into a `Uri`.
`StreamFactory::createStream()` returns a seekable, writable in-memory stream;
`createStreamFromFile()` opens a file for reading; `createStreamFromResource()` inspects the resource and
returns a stream whose capabilities match it.
