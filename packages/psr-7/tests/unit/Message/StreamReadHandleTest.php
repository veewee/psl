<?php

declare(strict_types=1);

namespace Psl\Psr\Http\Tests\Unit\Message;

use Psl\IO\MemoryHandle;
use Psl\Psr\Http\Message\Stream;
use Psl\Psr\Http\Message\StreamReadHandle;
use PHPUnit\Framework\TestCase;

final class StreamReadHandleTest extends TestCase
{
    public function testReadsChunksFromPsrStreamUntilEof(): void
    {
        $psr = Stream::fromHandle(new MemoryHandle('chunked data'), 12);
        $handle = new StreamReadHandle($psr);

        static::assertSame('chunk', $handle->read(5));
        static::assertSame('ed data', $handle->readAll());
        static::assertTrue($handle->reachedEndOfDataSource());
    }

    public function testReadWithNullMaxBytesReadsDefaultChunk(): void
    {
        $psr = Stream::fromHandle(new MemoryHandle('abc'), 3);
        $handle = new StreamReadHandle($psr);

        static::assertSame('abc', $handle->read());
    }
}
