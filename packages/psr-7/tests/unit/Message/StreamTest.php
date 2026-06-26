<?php

declare(strict_types=1);

namespace Psl\Psr\Http\Tests\Unit\Message;

use Psl\IO\MemoryHandle;
use Psl\Psr\Http\Message\Stream;
use PHPUnit\Framework\TestCase;

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
}
