<?php

declare(strict_types=1);

namespace Psl\Psr\Http\Tests\Unit\Factory;

use InvalidArgumentException;
use Psl\File;
use Psl\Filesystem;
use Psl\Psr\Http\Factory\StreamFactory;
use PHPUnit\Framework\TestCase;
use RuntimeException;

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

        try {
            $stream = (new StreamFactory())->createStreamFromFile($path);

            static::assertSame(13, $stream->getSize());
            static::assertSame('file contents', $stream->getContents());
            static::assertTrue($stream->isReadable());
            static::assertTrue($stream->isSeekable());   // files on disk are seekable
            static::assertFalse($stream->isWritable());   // 'r' mode
        } finally {
            Filesystem\delete_file($path);
        }
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

    // Fix #1: invalid mode must throw \InvalidArgumentException (PSR-17 requirement)
    public function testCreateStreamFromFileThrowsInvalidArgumentExceptionForInvalidMode(): void
    {
        $path = Filesystem\create_temporary_file();

        $this->expectException(InvalidArgumentException::class);
        try {
            (new StreamFactory())->createStreamFromFile($path, 'z');
        } finally {
            Filesystem\delete_file($path);
        }
    }

    // Fix #2: a file that cannot be opened must throw \RuntimeException (PSR-17 requirement)
    public function testCreateStreamFromFileThrowsRuntimeExceptionForNonExistentPath(): void
    {
        $this->expectException(RuntimeException::class);
        (new StreamFactory())->createStreamFromFile('/nonexistent/path/xyz_does_not_exist', 'r');
    }

    // Fix #3: seekable+readable+writable resource selects SeekReadWriteStreamHandle
    public function testCreateStreamFromReadWriteSeekableResourceIsReadableWritableAndSeekable(): void
    {
        $resource = fopen('php://temp', 'r+');

        $stream = (new StreamFactory())->createStreamFromResource($resource);

        static::assertTrue($stream->isReadable());
        static::assertTrue($stream->isWritable());
        static::assertTrue($stream->isSeekable());
    }
}
