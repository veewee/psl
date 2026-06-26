<?php

declare(strict_types=1);

namespace Psl\Psr\Http\Message;

use Psl\HTTP\Message\FieldMap;
use Psl\IO\MemoryHandle;
use Psl\Psr\Http\Message\Internal\MessageTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

final class Request implements RequestInterface
{
    use MessageTrait;

    private string $method;

    private ?string $requestTarget = null;

    private UriInterface $uri;

    public function __construct(string $method, UriInterface $uri, string $protocolVersion = '1.1')
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->protocolVersion = $protocolVersion;
        $this->fieldMap = FieldMap::default();
        $this->body = Stream::fromHandle(new MemoryHandle(''), 0);

        // Derive Host from URI on construction.
        $this->initHostFromUri($uri);
    }

    public function getRequestTarget(): string
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath();
        if ($target === '') {
            $target = '/';
        }

        $query = $this->uri->getQuery();
        if ($query !== '') {
            $target .= '?' . $query;
        }

        return $target;
    }

    public function withRequestTarget(string $requestTarget): static
    {
        $new = clone $this;
        $new->requestTarget = $requestTarget;

        return $new;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function withMethod(string $method): static
    {
        $new = clone $this;
        $new->method = $method;

        return $new;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false): static
    {
        $new = clone $this;
        $new->uri = $uri;

        if (!$preserveHost || !$this->hasHeader('Host')) {
            $new->initHostFromUri($uri);
        }

        return $new;
    }
}
