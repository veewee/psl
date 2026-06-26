<?php

declare(strict_types=1);

namespace Psl\Psr\Http\Message\Internal;

use InvalidArgumentException;
use Psl\HTTP\Message\FieldMap;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

use function implode;
use function is_array;
use function preg_match;
use function strtolower;
use function trim;

/**
 * Shared PSR-7 MessageInterface behavior for Request and Response.
 *
 * @internal
 */
trait MessageTrait
{
    private string $protocolVersion = '1.1';

    private FieldMap $fieldMap;

    private StreamInterface $body;

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion(string $version): static
    {
        $new = clone $this;
        $new->protocolVersion = $version;

        return $new;
    }

    /**
     * @return array<string, list<string>>
     */
    public function getHeaders(): array
    {
        $result = [];
        $canonical = [];
        foreach ($this->fieldMap->toArray() as [$name, $value]) {
            $lower = strtolower($name);
            if (!isset($canonical[$lower])) {
                $canonical[$lower] = $name;
            }
            $result[$canonical[$lower]][] = $value;
        }

        return $result;
    }

    public function hasHeader(string $name): bool
    {
        return $this->fieldMap->has($name);
    }

    /**
     * @return list<string>
     */
    public function getHeader(string $name): array
    {
        return $this->fieldMap->getAll($name);
    }

    public function getHeaderLine(string $name): string
    {
        return implode(', ', $this->fieldMap->getAll($name));
    }

    /**
     * @param string|list<string> $value
     *
     * The caller-supplied header name casing is preserved as-is, which is PSR-7-correct:
     * "the returned message MUST use the name exactly as provided".
     */
    public function withHeader(string $name, mixed $value): static
    {
        $values = $this->normalizeHeaderValue($name, $value);
        $new = clone $this;
        $new->fieldMap = $new->fieldMap->without($name);
        foreach ($values as $v) {
            $new->fieldMap = $new->fieldMap->withAdded($name, $v);
        }

        return $new;
    }

    /**
     * @param string|list<string> $value
     */
    public function withAddedHeader(string $name, mixed $value): static
    {
        $values = $this->normalizeHeaderValue($name, $value);
        $new = clone $this;
        foreach ($values as $v) {
            $new->fieldMap = $new->fieldMap->withAdded($name, $v);
        }

        return $new;
    }

    public function withoutHeader(string $name): static
    {
        $new = clone $this;
        $new->fieldMap = $new->fieldMap->without($name);

        return $new;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): static
    {
        $new = clone $this;
        $new->body = $body;

        return $new;
    }

    /**
     * Validate the header name (RFC 7230 token) and normalize the value to a list of trimmed strings.
     *
     * @param string|list<string>|mixed $value
     * @return list<string>
     */
    private function normalizeHeaderValue(string $name, mixed $value): array
    {
        self::assertValidHeaderName($name);

        if (!is_array($value)) {
            $value = [$value];
        }

        $result = [];
        foreach ($value as $v) {
            $v = (string) $v;
            self::assertValidHeaderValue($v);
            $result[] = trim($v, " \t");
        }

        return $result;
    }

    /**
     * Assert that a header name is a valid RFC 7230 token.
     *
     * @throws InvalidArgumentException
     */
    private static function assertValidHeaderName(string $name): void
    {
        if ($name === '' || preg_match("/^[!#\$%&'*+.^_`|~0-9A-Za-z-]+$/D", $name) !== 1) {
            throw new InvalidArgumentException(
                'Header name must be a non-empty RFC 7230 token; got "' . $name . '".',
            );
        }
    }

    /**
     * Assert that a header value contains only valid RFC 7230 field-value characters.
     *
     * @throws InvalidArgumentException
     */
    private static function assertValidHeaderValue(string $value): void
    {
        // field-value = *( ( %x21-7E / %x80-FF ) [ 1*( SP / HTAB ) ( %x21-7E / %x80-FF ) ] )
        // Allows SP/HTAB as internal whitespace but not control chars (%x00-1F, %x7F)
        if (preg_match('/[^\t\x20-\x7E\x80-\xFF]/', $value) === 1) {
            throw new InvalidArgumentException(
                'Header values must be RFC 7230 compatible strings.',
            );
        }
    }

    /**
     * Set the Host header as the first header in the field map, derived from the URI.
     * Used internally by Request during construction and withUri.
     */
    private function initHostFromUri(UriInterface $uri): void
    {
        $host = $uri->getHost();
        if ($host === '') {
            return;
        }

        $port = $uri->getPort();
        if ($port !== null) {
            $host .= ':' . $port;
        }

        // Host must be the first header per RFC 7230 section 5.4.
        // Remove existing Host and prepend.
        $withoutHost = $this->fieldMap->without('Host');
        $pairs = $withoutHost->toArray();
        // Prepend Host as first entry.
        $this->fieldMap = new FieldMap([['Host', $host], ...$pairs]);
    }
}
