# PSR-7

The `Psr\Http` component bridges PSR-7 and PSR-17 to `Psl\IO`. It lets a PSR-7 HTTP stack produce and consume `Psl\IO` handles, so code built on PSL streaming (such as `Psl\MIME`) can sit behind a PSR-7 interface without copying whole message bodies into memory.

This is the stream layer: the PSR-7 `StreamInterface` and the PSR-17 stream factory. Requests, responses, and URIs are not part of it.

## Stream

`Stream` wraps a `Psl\IO` handle as a PSR-7 `StreamInterface`. Its reported capabilities follow the wrapped handle: `isReadable()`, `isWritable()`, and `isSeekable()` each check whether the handle implements the matching `Psl\IO` interface.

    use Psl\IO\MemoryHandle;
    use Psl\Psr\Http\Message\Stream;

    $stream = Stream::fromHandle(new MemoryHandle('request body'), 12);

`getSize()` returns the byte count when it is known (a string length, a file size, or a spooled total) and `null` otherwise, because a `Psl\IO` handle does not carry its own size. `detach()` returns `null`, and `getMetadata()` returns `null` or an empty array, since a `Psl\IO` handle exposes no underlying resource or metadata. `seek()` accepts `SEEK_END` only when the size is known.

## Spooled Streams

When you need a known `Content-Length` and the ability to rewind for retries without keeping the whole body in memory, use `Stream::spooled()`. It drains the source handle into a `Psl\IO\spool()` buffer, which holds data in memory up to a threshold and then writes to a temporary file on disk.

    use Psl\Psr\Http\Message\Stream;

    $stream = Stream::spooled($readHandle);
    // seekable, getSize() is known, content spills to disk past the threshold

## StreamReadHandle

`StreamReadHandle` is the reverse adapter. It wraps a PSR-7 `StreamInterface` as a `Psl\IO\ReadHandleInterface`, so a PSL stream consumer can read a PSR-7 body. One use is feeding a PSR-7 response body into `Psl\MIME\Parser`.

    use Psl\Psr\Http\Message\StreamReadHandle;

    $handle = new StreamReadHandle($response->getBody());

It is read-only. `read()` delegates to the PSR-7 stream and falls back to an 8192-byte chunk when no size is given. `reachedEndOfDataSource()` maps to the stream's `eof()`.

## StreamFactory

`StreamFactory` implements the PSR-17 `StreamFactoryInterface`.

    use Psl\Psr\Http\Factory\StreamFactory;

    $factory = new StreamFactory();

    $fromString   = $factory->createStream('body');
    $fromFile     = $factory->createStreamFromFile('/path/to/file');
    $fromResource = $factory->createStreamFromResource($resource);

`createStream()` returns a seekable, writable in-memory stream. `createStreamFromFile()` opens the file for reading and returns a seekable stream. `createStreamFromResource()` inspects the resource and returns a stream whose capabilities match it.
