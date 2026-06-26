<?php

declare(strict_types=1);

namespace Psl\Psr\Http\Tests\Unit\Message;

use Psl\IO\MemoryHandle;
use Psl\Psr\Http\Message\Stream;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use const SEEK_CUR;
use const SEEK_END;

final class StreamTest extends TestCase
{
    public function testReadsContentAndReportsSize(): void
    {
        $stream = Stream::fromHandle(new MemoryHandle('hello world'), 11);

        static::assertTrue($stream->isReadable());
        static::assertSame(11, $stream->getSize());
        static::assertSame('hello', $stream->read(5));
        static::assertSame(' world', $stream->getContents());
        static::assertTrue($stream->eof());
    }

    public function testReadWithNonPositiveLengthReturnsEmptyString(): void
    {
        $stream = Stream::fromHandle(new MemoryHandle('data'));

        static::assertSame('', $stream->read(0));
        static::assertSame('', $stream->read(-3));
    }

    public function testWriteAppendsAndInvalidatesSize(): void
    {
        $handle = new MemoryHandle('');
        $stream = Stream::fromHandle($handle, 0);

        static::assertTrue($stream->isWritable());
        static::assertSame(5, $stream->write('hello'));
        static::assertNull($stream->getSize());
    }

    public function testGetSizeNullWhenUnknown(): void
    {
        static::assertNull(Stream::fromHandle(new MemoryHandle('x'))->getSize());
    }

    public function testSeekAndTellAbsoluteAndCurrent(): void
    {
        $stream = Stream::fromHandle(new MemoryHandle('0123456789'), 10);

        static::assertTrue($stream->isSeekable());
        $stream->seek(4);
        static::assertSame(4, $stream->tell());
        $stream->seek(2, SEEK_CUR);
        static::assertSame('6789', $stream->getContents());
    }

    public function testSeekEndRequiresKnownSize(): void
    {
        $known = Stream::fromHandle(new MemoryHandle('0123456789'), 10);
        $known->seek(-2, SEEK_END);
        static::assertSame('89', $known->getContents());

        $unknown = Stream::fromHandle(new MemoryHandle('0123456789'));
        $this->expectException(RuntimeException::class);
        $unknown->seek(-2, SEEK_END);
    }

    public function testToStringRewindsWhenSeekable(): void
    {
        $stream = Stream::fromHandle(new MemoryHandle('full body'), 9);
        $stream->read(4);

        static::assertSame('full body', (string) $stream);
    }

    public function testDetachReturnsNullAndDisablesStream(): void
    {
        $stream = Stream::fromHandle(new MemoryHandle('x'), 1);

        static::assertNull($stream->detach());
        static::assertFalse($stream->isReadable());
        static::assertNull($stream->getMetadata());
        static::assertSame([], $stream->getMetadata('size') ?? []);
    }

    public function testEofReturnsTrueOnDetachedStream(): void
    {
        $stream = Stream::fromHandle(new MemoryHandle('x'), 1);
        $stream->detach();

        static::assertTrue($stream->eof());
    }
}
