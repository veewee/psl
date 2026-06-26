<?php

declare(strict_types=1);

namespace Psl\Psr\Http\Tests\Unit\Message;

use PHPUnit\Framework\TestCase;
use Psl\Psr\Http\Factory\StreamFactory;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use const SEEK_CUR;

final class StreamConformanceTest extends TestCase
{
    private function stream(string $data = ''): StreamInterface
    {
        return (new StreamFactory())->createStream($data);
    }

    public function testCapabilitiesForMemoryBackedStream(): void
    {
        $s = $this->stream('abc');
        static::assertTrue($s->isReadable());
        static::assertTrue($s->isWritable());
        static::assertTrue($s->isSeekable());
    }

    public function testWriteThenReadRoundTrip(): void
    {
        $s = $this->stream();
        static::assertSame(5, $s->write('hello'));
        $s->rewind();
        static::assertSame('hello', $s->getContents());
    }

    public function testTellSeekRewind(): void
    {
        $s = $this->stream('0123456789');
        $s->seek(4);
        static::assertSame(4, $s->tell());
        $s->seek(2, SEEK_CUR);
        static::assertSame(6, $s->tell());
        $s->rewind();
        static::assertSame(0, $s->tell());
    }

    public function testGetContentsFromOffset(): void
    {
        $s = $this->stream('0123456789');
        $s->seek(7);
        static::assertSame('789', $s->getContents());
    }

    public function testEofAfterReadingAll(): void
    {
        $s = $this->stream('xy');
        $s->getContents();
        static::assertTrue($s->eof());
    }

    public function testToStringReturnsFullBody(): void
    {
        $s = $this->stream('full body');
        $s->read(4);
        static::assertSame('full body', (string) $s);
    }

    public function testCloseThenOperationsThrow(): void
    {
        $s = $this->stream('data');
        $s->close();
        $this->expectException(RuntimeException::class);
        $s->read(1);
    }

    public function testDetachReturnsNull(): void
    {
        static::assertNull($this->stream('data')->detach());
    }
}
