<?php

declare(strict_types=1);

namespace Psl\Psr\Http\Factory;

use Psl\Psr\Http\Message\Request;
use Psl\Psr\Http\Message\Uri;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

final class RequestFactory implements RequestFactoryInterface
{
    public function createRequest(string $method, mixed $uri): RequestInterface
    {
        if (!($uri instanceof UriInterface)) {
            $uri = Uri::fromString((string) $uri);
        }

        return new Request($method, $uri);
    }
}
