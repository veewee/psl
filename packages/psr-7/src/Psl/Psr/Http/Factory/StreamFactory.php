<?php

declare(strict_types=1);

namespace Psl\Psr\Http\Factory;

use Psl\Filesystem;
use Psl\IO\CloseSeekReadStreamHandle;
use Psl\IO\MemoryHandle;
use Psl\IO\ReadStreamHandle;
use Psl\IO\SeekReadStreamHandle;
use Psl\IO\SeekReadWriteStreamHandle;
use Psl\Psr\Http\Message\Stream;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

use function fopen;
use function strlen;
use function strpbrk;
use function stream_get_meta_data;

final class StreamFactory implements StreamFactoryInterface
{
    public function createStream(string $content = ''): StreamInterface
    {
        // MemoryHandle is read + write + seek, so the resulting Stream is fully capable.
        return Stream::fromHandle(new MemoryHandle($content), strlen($content));
    }

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        $resource = @fopen($filename, $mode);
        if ($resource === false) {
            throw new RuntimeException("Unable to open '{$filename}' with mode '{$mode}'.");
        }

        $size = Filesystem\is_file($filename) ? Filesystem\file_size($filename) : null;

        // Slice 1 supports readable modes ('r', 'rb', 'r+', …). Files are seekable; we own the
        // resource, so close it on Stream::close(). Write/append-only modes are deferred (O3).
        return Stream::fromHandle(new CloseSeekReadStreamHandle($resource), $size);
    }

    public function createStreamFromResource($resource): StreamInterface
    {
        $meta = stream_get_meta_data($resource);
        $seekable = (bool) ($meta['seekable'] ?? false);
        $writable = false !== strpbrk($meta['mode'] ?? 'r', 'xwca+');

        // The user owns this resource — never close it (all branches are no-close handles).
        $handle = match (true) {
            $seekable && $writable => new SeekReadWriteStreamHandle($resource),
            $seekable              => new SeekReadStreamHandle($resource),
            default                => new ReadStreamHandle($resource),
        };

        return Stream::fromHandle($handle);
    }
}
