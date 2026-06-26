<?php

declare(strict_types=1);

namespace Psl\Psr\Http\Tests\Unit\Message;

use Psl\IO\MemoryHandle;
use Psl\Psr\Http\Message\Stream;
use PHPUnit\Framework\TestCase;

use function str_repeat;
use function strlen;

final class StreamSpooledTest extends TestCase
{
    public function testSpooledIsSizedSeekableAndRewound(): void
    {
        $stream = Stream::spooled(new MemoryHandle('multipart body'));

        static::assertTrue($stream->isSeekable());
        static::assertSame(14, $stream->getSize());
        static::assertSame(0, $stream->tell());
        static::assertSame('multipart body', $stream->getContents());
    }

    public function testSpooledHandlesContentLargerThanThreshold(): void
    {
        $payload = str_repeat('A', 100_000);
        $stream = Stream::spooled(new MemoryHandle($payload), threshold: 1024);

        static::assertSame(strlen($payload), $stream->getSize());
        static::assertSame($payload, $stream->getContents());
    }
}
