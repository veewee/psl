<?php

declare(strict_types=1);

namespace Psl\Psr\Http\Factory;

use InvalidArgumentException;
use Psl\File;
use Psl\Filesystem;
use Psl\IO\MemoryHandle;
use Psl\IO\ReadStreamHandle;
use Psl\IO\ReadWriteStreamHandle;
use Psl\IO\SeekReadStreamHandle;
use Psl\IO\SeekReadWriteStreamHandle;
use Psl\IO\SeekWriteStreamHandle;
use Psl\IO\WriteStreamHandle;
use Psl\Psr\Http\Message\Stream;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

use function preg_match;
use function str_contains;
use function strlen;
use function stream_get_meta_data;

final class StreamFactory implements StreamFactoryInterface
{
    public function createStream(string $content = ''): StreamInterface
    {
        // MemoryHandle is read, write, and seek capable, so the resulting Stream is fully capable.
        return Stream::fromHandle(new MemoryHandle($content), strlen($content));
    }

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        // PSR-17 requires InvalidArgumentException for a malformed mode.
        if ($filename === '' || !preg_match('/^[rwaxc](?:\+[bt]?|[bt]\+?)?$/', $mode)) {
            throw new InvalidArgumentException("Invalid file mode '{$mode}' or empty filename.");
        }

        // Slice 1 supports reading existing files. Write and append modes are deferred (O3).
        if (str_contains($mode, 'w') || str_contains($mode, 'a') || str_contains($mode, 'x') || str_contains($mode, 'c')) {
            throw new RuntimeException("File mode '{$mode}' is not supported yet; only read modes are available.");
        }

        try {
            // Psl\File\open_read_only handles the error cases with typed exceptions and
            // returns a seekable, closeable read handle.
            $handle = File\open_read_only($filename);
        } catch (File\Exception\ExceptionInterface $e) {
            // PSR-17 requires RuntimeException when the file cannot be opened.
            throw new RuntimeException("Unable to open '{$filename}': " . $e->getMessage(), previous: $e);
        }

        $size = Filesystem\is_file($filename) ? Filesystem\file_size($filename) : null;

        return Stream::fromHandle($handle, $size);
    }

    public function createStreamFromResource($resource): StreamInterface
    {
        $meta = stream_get_meta_data($resource);
        $resourceMode = $meta['mode'] ?? 'r';
        $seekable = (bool) ($meta['seekable'] ?? false);
        $readable = str_contains($resourceMode, 'r') || str_contains($resourceMode, '+');
        $writable = (bool) preg_match('/[xwac+]/', $resourceMode);

        // The user owns this resource, so it is never closed (every branch is a no-close handle).
        $handle = match (true) {
            $seekable && $readable && $writable => new SeekReadWriteStreamHandle($resource),
            $seekable && $readable              => new SeekReadStreamHandle($resource),
            $seekable && $writable              => new SeekWriteStreamHandle($resource),
            $readable && $writable              => new ReadWriteStreamHandle($resource),
            $readable                           => new ReadStreamHandle($resource),
            default                             => new WriteStreamHandle($resource),
        };

        return Stream::fromHandle($handle);
    }
}
