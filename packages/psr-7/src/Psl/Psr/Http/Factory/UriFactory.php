<?php

declare(strict_types=1);

namespace Psl\Psr\Http\Factory;

use InvalidArgumentException;
use Psl\Psr\Http\Message\Uri;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

final class UriFactory implements UriFactoryInterface
{
    /**
     * Create a new URI from a string.
     *
     * @throws InvalidArgumentException If $uri is not a valid RFC 3986 URI.
     */
    public function createUri(string $uri = ''): UriInterface
    {
        return Uri::fromString($uri);
    }
}
