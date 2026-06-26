<?php

declare(strict_types=1);

namespace Psl\Psr\Http\Message;

use InvalidArgumentException;
use Psl\URI\Authority\Authority;
use Psl\URI\Authority\RegisteredNameHost;
use Psl\URI\Exception\InvalidURIException;
use Psl\URI\PathKind;
use Psl\URI\URI as PslURI;
use Psr\Http\Message\UriInterface;
use SensitiveParameter;

use function array_key_exists;
use function str_starts_with;
use function strtolower;

use function Psl\URI\parse;

/**
 * PSR-7 UriInterface implementation built on Psl\URI.
 *
 * Stores decomposed string components internally. Psl\URI\parse() is used
 * only at parse-time; withX mutations recompose cheaply without re-parsing.
 */
final class Uri implements UriInterface
{
    /**
     * Default ports for well-known schemes. When the explicit port matches the
     * scheme default, getPort() returns null and getAuthority() omits the port.
     *
     * @var array<string, int>
     */
    private const array DEFAULT_PORTS = [
        'http'  => 80,
        'https' => 443,
        'ftp'   => 21,
        'ssh'   => 22,
        'smtp'  => 25,
        'pop'   => 110,
        'imap'  => 143,
        'ldap'  => 389,
        'ldaps' => 636,
        'ftps'  => 990,
    ];

    /**
     * @param string      $scheme   Lowercased scheme, empty string if absent.
     * @param string|null $userInfo null = absent, string = present (possibly empty).
     * @param string      $host     Lowercased host, empty string if absent.
     * @param int|null    $port     Explicit port; null = absent. Standard-port
     *                             suppression happens at getter time, not storage time.
     * @param string      $path     Path component.
     * @param string|null $query    null = absent, string = present (possibly empty).
     * @param string|null $fragment null = absent, string = present (possibly empty).
     */
    private function __construct(
        private readonly string $scheme,
        private readonly string|null $userInfo,
        private readonly string $host,
        private readonly int|null $port,
        private readonly string $path,
        private readonly string|null $query,
        private readonly string|null $fragment,
    ) {}

    /**
     * Parse a URI string via Psl\URI\parse() and return a new Uri instance.
     *
     * @throws InvalidArgumentException If $uri is not a valid RFC 3986 URI.
     */
    public static function fromString(string $uri): self
    {
        if ($uri === '') {
            return new self('', null, '', null, '', null, null);
        }

        try {
            $parsed = parse($uri);
        } catch (InvalidURIException $e) {
            throw new InvalidArgumentException(
                'Invalid URI "' . $uri . '": ' . $e->getMessage(),
                previous: $e,
            );
        }

        return self::fromPslUri($parsed);
    }

    /**
     * Build a Uri from a parsed Psl\URI\URI value object.
     */
    private static function fromPslUri(PslURI $parsed): self
    {
        $scheme = $parsed->scheme ?? '';
        $authority = $parsed->authority;
        $userInfo = null;
        $host = '';
        $port = null;

        if ($authority !== null) {
            $userInfo = $authority->userInfo;
            $host = $authority->host->toString();
            $port = $authority->port;
        }

        return new self(
            $scheme,
            $userInfo,
            $host,
            $port,
            $parsed->path,
            $parsed->query,
            $parsed->fragment,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthority(): string
    {
        if ($this->host === '') {
            return '';
        }

        $authority = '';

        $userInfo = $this->getUserInfo();
        if ($userInfo !== '') {
            $authority .= $userInfo . '@';
        }

        $authority .= $this->host;

        $port = $this->getPort();
        if ($port !== null) {
            $authority .= ':' . $port;
        }

        return $authority;
    }

    /**
     * {@inheritDoc}
     */
    public function getUserInfo(): string
    {
        return $this->userInfo ?? '';
    }

    /**
     * {@inheritDoc}
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * {@inheritDoc}
     *
     * Returns null when the port is absent or when it equals the default for
     * the current scheme.
     */
    public function getPort(): int|null
    {
        if ($this->port === null) {
            return null;
        }

        if ($this->scheme !== '' && array_key_exists($this->scheme, self::DEFAULT_PORTS)) {
            if ($this->port === self::DEFAULT_PORTS[$this->scheme]) {
                return null;
            }
        }

        return $this->port;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritDoc}
     */
    public function getQuery(): string
    {
        return $this->query ?? '';
    }

    /**
     * {@inheritDoc}
     */
    public function getFragment(): string
    {
        return $this->fragment ?? '';
    }

    /**
     * {@inheritDoc}
     */
    public function withScheme(string $scheme): UriInterface
    {
        $scheme = strtolower($scheme);

        return new self(
            $scheme,
            $this->userInfo,
            $this->host,
            $this->port,
            $this->path,
            $this->query,
            $this->fragment,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function withUserInfo(string $user, #[SensitiveParameter] string|null $password = null): UriInterface
    {
        if ($user === '') {
            $userInfo = null;
        } elseif ($password !== null && $password !== '') {
            $userInfo = $user . ':' . $password;
        } else {
            $userInfo = $user;
        }

        return new self(
            $this->scheme,
            $userInfo,
            $this->host,
            $this->port,
            $this->path,
            $this->query,
            $this->fragment,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function withHost(string $host): UriInterface
    {
        return new self(
            $this->scheme,
            $this->userInfo,
            strtolower($host),
            $this->port,
            $this->path,
            $this->query,
            $this->fragment,
        );
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException If $port is outside [0, 65535].
     */
    public function withPort(int|null $port): UriInterface
    {
        if ($port !== null && ($port < 0 || $port > 65_535)) {
            throw new InvalidArgumentException(
                'Invalid port ' . $port . ': must be null or an integer in the range [0, 65535].',
            );
        }

        return new self(
            $this->scheme,
            $this->userInfo,
            $this->host,
            $port,
            $this->path,
            $this->query,
            $this->fragment,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function withPath(string $path): UriInterface
    {
        return new self(
            $this->scheme,
            $this->userInfo,
            $this->host,
            $this->port,
            $path,
            $this->query,
            $this->fragment,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function withQuery(string $query): UriInterface
    {
        return new self(
            $this->scheme,
            $this->userInfo,
            $this->host,
            $this->port,
            $this->path,
            $query === '' ? null : $query,
            $this->fragment,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function withFragment(string $fragment): UriInterface
    {
        return new self(
            $this->scheme,
            $this->userInfo,
            $this->host,
            $this->port,
            $this->path,
            $this->query,
            $fragment === '' ? null : $fragment,
        );
    }

    /**
     * RFC 3986 Section 5.3 recomposition.
     *
     * Replicates the logic in Psl\URI\URI::toString() but applies standard-port
     * suppression for the PSR-7 layer so the serialized form matches getPort().
     */
    public function __toString(): string
    {
        // Build a Psl\URI\URI with the standard-port suppressed and delegate
        // to its toString() for correct recomposition.
        $effectivePort = $this->getPort();

        $authority = null;
        if ($this->host !== '') {
            $host = new RegisteredNameHost($this->host);
            $userInfo = $this->userInfo;
            $authority = new Authority($userInfo, $host, $effectivePort);
        }

        $pathKind = match (true) {
            $this->path === '' => PathKind::None,
            str_starts_with($this->path, '/') => PathKind::Absolute,
            default => PathKind::Rootless,
        };

        $pslUri = new PslURI(
            $this->scheme !== '' ? $this->scheme : null,
            $authority,
            $this->path,
            $pathKind,
            $this->query,
            $this->fragment,
        );

        return $pslUri->toString();
    }
}
