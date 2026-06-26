<?php

declare(strict_types=1);

namespace Psl\Psr\Http\Message;

use Throwable;
use Psl\IO;
use Psl\IO\CloseHandleInterface;
use Psl\IO\HandleInterface;
use Psl\IO\ReadHandleInterface;
use Psl\IO\SeekHandleInterface;
use Psl\IO\WriteHandleInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

use const SEEK_CUR;
use const SEEK_END;
use const SEEK_SET;

use function strlen;

final class Stream implements StreamInterface
{
    private bool $detached = false;

    private function __construct(
        private readonly HandleInterface $handle,
        private ?int $size,
    ) {}

    public static function fromHandle(HandleInterface $handle, ?int $size = null): self
    {
        return new self($handle, $size);
    }

    public static function spooled(ReadHandleInterface $source, int $threshold = 2_097_152): self
    {
        $spool = IO\spool($threshold);
        $spool->writeAll($source->readAll());
        $size = $spool->tell();
        $spool->seek(0);

        return new self($spool, $size);
    }

    public function isReadable(): bool
    {
        return !$this->detached && $this->handle instanceof ReadHandleInterface;
    }

    public function isWritable(): bool
    {
        return !$this->detached && $this->handle instanceof WriteHandleInterface;
    }

    public function isSeekable(): bool
    {
        return !$this->detached && $this->handle instanceof SeekHandleInterface;
    }

    public function read(int $length): string
    {
        $handle = $this->readable();
        if ($length <= 0) {
            return '';
        }

        /** @var positive-int $length - narrowed by the guard above (satisfies Mago) */
        return $handle->read($length);
    }

    public function getContents(): string
    {
        return $this->readable()->readAll();
    }

    public function write(string $string): int
    {
        if ($this->detached || !$this->handle instanceof WriteHandleInterface) {
            throw new RuntimeException('Stream is not writable.');
        }

        $this->handle->writeAll($string);
        // Conservative: any write makes the total length unknown. getSize() === null is
        // always spec-valid ("size when known"); we do not attempt to track overwrite vs append.
        $this->size = null;

        return strlen($string);
    }

    public function eof(): bool
    {
        if ($this->detached || !$this->handle instanceof ReadHandleInterface) {
            return true;
        }

        return $this->handle->reachedEndOfDataSource();
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function tell(): int
    {
        return $this->seekable()->tell();
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        $handle = $this->seekable();

        $target = match ($whence) {
            SEEK_SET => $offset,
            SEEK_CUR => $handle->tell() + $offset,
            SEEK_END => $this->sizeForEnd() + $offset,
            default => throw new RuntimeException('Invalid seek whence.'),
        };

        if ($target < 0) {
            throw new RuntimeException('Cannot seek to a negative position.');
        }

        $handle->seek($target);
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function __toString(): string
    {
        try {
            if ($this->isSeekable()) {
                $this->rewind();
            }

            return $this->getContents();
        } catch (Throwable) {
            return '';
        }
    }

    public function close(): void
    {
        if (!$this->detached && $this->handle instanceof CloseHandleInterface) {
            $this->handle->close();
        }

        $this->detached = true;
    }

    public function detach(): mixed
    {
        $this->detached = true;

        return null;
    }

    public function getMetadata(?string $key = null): mixed
    {
        if ($this->detached) {
            return null;
        }

        return $key === null ? [] : null;
    }

    private function seekable(): SeekHandleInterface
    {
        if ($this->detached || !$this->handle instanceof SeekHandleInterface) {
            throw new RuntimeException('Stream is not seekable.');
        }

        return $this->handle;
    }

    private function sizeForEnd(): int
    {
        if ($this->size === null) {
            throw new RuntimeException('Cannot SEEK_END: stream size is unknown.');
        }

        return $this->size;
    }

    private function readable(): ReadHandleInterface
    {
        if ($this->detached || !$this->handle instanceof ReadHandleInterface) {
            throw new RuntimeException('Stream is not readable.');
        }

        return $this->handle;
    }
}
