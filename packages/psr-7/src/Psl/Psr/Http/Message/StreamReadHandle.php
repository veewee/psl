<?php

declare(strict_types=1);

namespace Psl\Psr\Http\Message;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\IO\ReadHandleConvenienceMethodsTrait;
use Psl\IO\ReadHandleInterface;
use Psr\Http\Message\StreamInterface;

final class StreamReadHandle implements ReadHandleInterface
{
    use ReadHandleConvenienceMethodsTrait;

    private const int DEFAULT_CHUNK = 8192;

    public function __construct(
        private StreamInterface $stream,
    ) {}

    public function tryRead(?int $maxBytes = null): string
    {
        return $this->stream->read($maxBytes ?? self::DEFAULT_CHUNK);
    }

    public function read(
        ?int $maxBytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        return $this->stream->read($maxBytes ?? self::DEFAULT_CHUNK);
    }

    public function reachedEndOfDataSource(): bool
    {
        return $this->stream->eof();
    }
}
