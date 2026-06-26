<?php

declare(strict_types=1);

namespace Psl\Psr\Http\Tests\Unit\Factory;

use Psl\File;
use Psl\Filesystem;
use Psl\Psr\Http\Factory\StreamFactory;
use PHPUnit\Framework\TestCase;

use function fwrite;
use function rewind;
use function fopen;

final class StreamFactoryTest extends TestCase
{
    public function testCreateStreamFromStringIsSizedSeekableAndWritable(): void
    {
        $stream = (new StreamFactory())->createStream('hello');

        static::assertSame(5, $stream->getSize());
        static::assertSame('hello', (string) $stream);
        // MemoryHandle is read + write + seek
        static::assertTrue($stream->isReadable());
        static::assertTrue($stream->isWritable());
        static::assertTrue($stream->isSeekable());
    }

    public function testCreateStreamFromFileIsReadableAndSeekable(): void
    {
        $path = Filesystem\create_temporary_file();
        File\write($path, 'file contents');

        $stream = (new StreamFactory())->createStreamFromFile($path);

        static::assertSame(13, $stream->getSize());
        static::assertSame('file contents', $stream->getContents());
        static::assertTrue($stream->isReadable());
        static::assertTrue($stream->isSeekable());   // files on disk are seekable
        static::assertFalse($stream->isWritable());   // 'r' mode

        Filesystem\delete_file($path);
    }

    public function testCreateStreamFromSeekableResourceIsSeekable(): void
    {
        $resource = fopen('php://temp', 'r+b');
        fwrite($resource, 'resource data');
        rewind($resource);

        $stream = (new StreamFactory())->createStreamFromResource($resource);

        static::assertSame('resource data', $stream->getContents());
        static::assertTrue($stream->isSeekable());
        $stream->rewind();
        static::assertSame('resource', $stream->read(8));
    }
}
