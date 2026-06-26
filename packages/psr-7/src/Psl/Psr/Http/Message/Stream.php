<?php

declare(strict_types=1);

namespace Psl\Psr\Http\Message;

use Psl\IO\HandleInterface;
use Psl\IO\ReadHandleInterface;
use Psl\IO\SeekHandleInterface;
use Psl\IO\WriteHandleInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

use const SEEK_SET;

use function strlen;

final class Stream implements StreamInterface
{
    private bool $detached = false;

    private function __construct(
        private HandleInterface $handle,
        private ?int $size,
    ) {}

    public static function fromHandle(HandleInterface $handle, ?int $size = null): self
    {
        return new self($handle, $size);
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
        return $this->readable()->reachedEndOfDataSource();
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function __toString(): string { throw new RuntimeException('not implemented'); }
    public function close(): void { throw new RuntimeException('not implemented'); }
    public function detach() { throw new RuntimeException('not implemented'); }
    public function tell(): int { throw new RuntimeException('not implemented'); }
    public function seek(int $offset, int $whence = SEEK_SET): void { throw new RuntimeException('not implemented'); }
    public function rewind(): void { throw new RuntimeException('not implemented'); }
    public function getMetadata(?string $key = null) { throw new RuntimeException('not implemented'); }

    private function readable(): ReadHandleInterface
    {
        if ($this->detached || !$this->handle instanceof ReadHandleInterface) {
            throw new RuntimeException('Stream is not readable.');
        }

        return $this->handle;
    }
}
