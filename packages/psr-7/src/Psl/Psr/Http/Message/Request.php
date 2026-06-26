<?php

declare(strict_types=1);

namespace Psl\Psr\Http\Message;

use InvalidArgumentException;
use Psl\HTTP\Message\FieldMap;

use function preg_match;
use Psl\IO\MemoryHandle;
use Psl\Psr\Http\Message\Internal\MessageTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

final class Request implements RequestInterface
{
    use MessageTrait;

    private ?string $requestTarget = null;

    public function __construct(
        private string $method,
        private UriInterface $uri,
        string $protocolVersion = '1.1',
    ) {
        self::assertValidMethod($method);
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
        self::assertValidMethod($method);
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

    /**
     * Assert that an HTTP method is a valid RFC 7230 token.
     *
     * @throws InvalidArgumentException
     */
    private static function assertValidMethod(string $method): void
    {
        if ($method === '' || preg_match("/^[!#\$%&'*+.^_`|~0-9A-Za-z-]+$/D", $method) !== 1) {
            throw new InvalidArgumentException(
                'HTTP method must be a non-empty RFC 7230 token; got "' . $method . '".',
            );
        }
    }
}
