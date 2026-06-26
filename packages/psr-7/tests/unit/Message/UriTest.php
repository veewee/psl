<?php

declare(strict_types=1);

namespace Psl\Psr\Http\Tests\Unit\Message;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psl\Psr\Http\Message\Uri;

final class UriTest extends TestCase
{
    // -----------------------------------------------------------------------
    // Full absolute URL round-trip
    // -----------------------------------------------------------------------

    public function testFullAbsoluteUrlGetters(): void
    {
        $uri = Uri::fromString('https://user:pass@host.example:8080/p/a?x=1#frag');

        static::assertSame('https', $uri->getScheme());
        static::assertSame('user:pass', $uri->getUserInfo());
        static::assertSame('host.example', $uri->getHost());
        static::assertSame(8080, $uri->getPort());
        static::assertSame('user:pass@host.example:8080', $uri->getAuthority());
        static::assertSame('/p/a', $uri->getPath());
        static::assertSame('x=1', $uri->getQuery());
        static::assertSame('frag', $uri->getFragment());
    }

    public function testFullAbsoluteUrlToString(): void
    {
        $raw = 'https://user:pass@host.example:8080/p/a?x=1#frag';
        $uri = Uri::fromString($raw);

        static::assertSame($raw, (string) $uri);
    }

    // -----------------------------------------------------------------------
    // Standard-port suppression
    // -----------------------------------------------------------------------

    public function testHttpsStandardPortSuppressed(): void
    {
        $uri = Uri::fromString('https://h:443/');

        static::assertNull($uri->getPort());
        static::assertSame('h', $uri->getAuthority());
    }

    public function testHttpStandardPortSuppressed(): void
    {
        $uri = Uri::fromString('http://h:80/');

        static::assertNull($uri->getPort());
        static::assertSame('h', $uri->getAuthority());
    }

    public function testNonStandardPortKept(): void
    {
        $uri = Uri::fromString('https://h:8443/');

        static::assertSame(8443, $uri->getPort());
        static::assertSame('h:8443', $uri->getAuthority());
    }

    // -----------------------------------------------------------------------
    // Scheme and host normalization (lowercased)
    // -----------------------------------------------------------------------

    public function testSchemeIsLowercased(): void
    {
        $uri = Uri::fromString('HTTPS://example.com/');

        static::assertSame('https', $uri->getScheme());
    }

    public function testHostIsLowercased(): void
    {
        $uri = Uri::fromString('https://EXAMPLE.COM/');

        static::assertSame('example.com', $uri->getHost());
    }

    // -----------------------------------------------------------------------
    // Relative references
    // -----------------------------------------------------------------------

    public function testPathOnlyRelativeRef(): void
    {
        $uri = Uri::fromString('/path/only');

        static::assertSame('', $uri->getScheme());
        static::assertSame('', $uri->getAuthority());
        static::assertSame('', $uri->getHost());
        static::assertNull($uri->getPort());
        static::assertSame('/path/only', $uri->getPath());
        static::assertSame('', $uri->getQuery());
        static::assertSame('', $uri->getFragment());
    }

    public function testQueryOnlyRelativeRef(): void
    {
        $uri = Uri::fromString('?just=query');

        static::assertSame('', $uri->getScheme());
        static::assertSame('just=query', $uri->getQuery());
        static::assertSame('', $uri->getFragment());
    }

    public function testFragmentOnlyRelativeRef(): void
    {
        $uri = Uri::fromString('#frag');

        static::assertSame('', $uri->getScheme());
        static::assertSame('', $uri->getQuery());
        static::assertSame('frag', $uri->getFragment());
    }

    public function testEmptyUri(): void
    {
        $uri = Uri::fromString('');

        static::assertSame('', $uri->getScheme());
        static::assertSame('', $uri->getAuthority());
        static::assertSame('', $uri->getHost());
        static::assertNull($uri->getPort());
        static::assertSame('', $uri->getPath());
        static::assertSame('', $uri->getQuery());
        static::assertSame('', $uri->getFragment());
        static::assertSame('', (string) $uri);
    }

    // -----------------------------------------------------------------------
    // withScheme
    // -----------------------------------------------------------------------

    public function testWithSchemeReturnsNewInstance(): void
    {
        $original = Uri::fromString('http://example.com/');
        $modified = $original->withScheme('https');

        static::assertNotSame($original, $modified);
        static::assertSame('http', $original->getScheme());
        static::assertSame('https', $modified->getScheme());
    }

    public function testWithSchemeLowercases(): void
    {
        $uri = Uri::fromString('http://example.com/')->withScheme('HTTPS');

        static::assertSame('https', $uri->getScheme());
    }

    public function testWithSchemeEmpty(): void
    {
        $uri = Uri::fromString('http://example.com/')->withScheme('');

        static::assertSame('', $uri->getScheme());
    }

    // -----------------------------------------------------------------------
    // withUserInfo
    // -----------------------------------------------------------------------

    public function testWithUserInfoSetsUserAndPassword(): void
    {
        $uri = Uri::fromString('https://example.com/')->withUserInfo('user', 'pass');

        static::assertSame('user:pass', $uri->getUserInfo());
        static::assertSame('user:pass@example.com', $uri->getAuthority());
    }

    public function testWithUserInfoPasswordNull(): void
    {
        $uri = Uri::fromString('https://example.com/')->withUserInfo('user');

        static::assertSame('user', $uri->getUserInfo());
    }

    public function testWithUserInfoClearsWhenEmpty(): void
    {
        $original = Uri::fromString('https://user:pass@example.com/');
        $modified = $original->withUserInfo('');

        static::assertSame('', $modified->getUserInfo());
        static::assertSame('example.com', $modified->getAuthority());
    }

    public function testWithUserInfoImmutable(): void
    {
        $original = Uri::fromString('https://user:pass@example.com/');
        $original->withUserInfo('other', 'secret');

        static::assertSame('user:pass', $original->getUserInfo());
    }

    // -----------------------------------------------------------------------
    // withHost
    // -----------------------------------------------------------------------

    public function testWithHostReturnsNewInstance(): void
    {
        $original = Uri::fromString('https://example.com/');
        $modified = $original->withHost('other.org');

        static::assertNotSame($original, $modified);
        static::assertSame('example.com', $original->getHost());
        static::assertSame('other.org', $modified->getHost());
    }

    public function testWithHostLowercases(): void
    {
        $uri = Uri::fromString('https://example.com/')->withHost('OTHER.ORG');

        static::assertSame('other.org', $uri->getHost());
    }

    // -----------------------------------------------------------------------
    // withPort
    // -----------------------------------------------------------------------

    public function testWithPortSetsExplicitPort(): void
    {
        $uri = Uri::fromString('https://example.com/')->withPort(8443);

        static::assertSame(8443, $uri->getPort());
    }

    public function testWithPortNullClearsPort(): void
    {
        $uri = Uri::fromString('https://example.com:8443/')->withPort(null);

        static::assertNull($uri->getPort());
    }

    public function testWithPortZeroIsValid(): void
    {
        $uri = Uri::fromString('https://example.com/')->withPort(0);

        static::assertSame(0, $uri->getPort());
    }

    public function testWithPortMaxValidValue(): void
    {
        $uri = Uri::fromString('https://example.com/')->withPort(65_535);

        static::assertSame(65_535, $uri->getPort());
    }

    public function testWithPortTooHighThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Uri::fromString('https://example.com/')->withPort(70_000);
    }

    public function testWithPortNegativeThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Uri::fromString('https://example.com/')->withPort(-1);
    }

    public function testWithPortImmutable(): void
    {
        $original = Uri::fromString('https://example.com:8443/');
        $original->withPort(9000);

        static::assertSame(8443, $original->getPort());
    }

    // -----------------------------------------------------------------------
    // withPath
    // -----------------------------------------------------------------------

    public function testWithPathReturnsNewInstance(): void
    {
        $original = Uri::fromString('https://example.com/old');
        $modified = $original->withPath('/new');

        static::assertNotSame($original, $modified);
        static::assertSame('/old', $original->getPath());
        static::assertSame('/new', $modified->getPath());
    }

    public function testWithPathRecomposesCorrectly(): void
    {
        $uri = Uri::fromString('https://example.com/')->withPath('/new/path');

        static::assertSame('https://example.com/new/path', (string) $uri);
    }

    // -----------------------------------------------------------------------
    // withQuery
    // -----------------------------------------------------------------------

    public function testWithQueryReturnsNewInstance(): void
    {
        $original = Uri::fromString('https://example.com/?a=1');
        $modified = $original->withQuery('b=2');

        static::assertNotSame($original, $modified);
        static::assertSame('a=1', $original->getQuery());
        static::assertSame('b=2', $modified->getQuery());
    }

    public function testWithQueryEmptyStringRemovesQuery(): void
    {
        // PSR-7: "An empty query string value is equivalent to removing the query string"
        $uri = Uri::fromString('https://example.com/?a=1')->withQuery('');

        static::assertSame('', $uri->getQuery());
        // No '?' in the serialized form: query is absent, not empty-present
        static::assertStringNotContainsString('?', (string) $uri);
    }

    public function testWithFragmentEmptyStringRemovesFragment(): void
    {
        // PSR-7: empty fragment value is equivalent to removing the fragment
        $uri = Uri::fromString('https://example.com/#old')->withFragment('');

        static::assertSame('', $uri->getFragment());
        // No '#' in the serialized form: fragment is absent, not empty-present
        static::assertStringNotContainsString('#', (string) $uri);
    }

    // -----------------------------------------------------------------------
    // withFragment
    // -----------------------------------------------------------------------

    public function testWithFragmentReturnsNewInstance(): void
    {
        $original = Uri::fromString('https://example.com/#old');
        $modified = $original->withFragment('new');

        static::assertNotSame($original, $modified);
        static::assertSame('old', $original->getFragment());
        static::assertSame('new', $modified->getFragment());
    }

    public function testWithFragmentImmutable(): void
    {
        $original = Uri::fromString('https://example.com/#old');
        $original->withFragment('new');

        static::assertSame('old', $original->getFragment());
    }

    // -----------------------------------------------------------------------
    // Empty query/fragment vs absent (null semantics in Psl\URI)
    // -----------------------------------------------------------------------

    public function testAbsentQueryReturnsEmptyString(): void
    {
        $uri = Uri::fromString('https://example.com/');

        static::assertSame('', $uri->getQuery());
    }

    public function testAbsentFragmentReturnsEmptyString(): void
    {
        $uri = Uri::fromString('https://example.com/');

        static::assertSame('', $uri->getFragment());
    }

    public function testAbsentQueryDoesNotAddQuestionMarkInToString(): void
    {
        $uri = Uri::fromString('https://example.com/path');

        static::assertStringNotContainsString('?', (string) $uri);
    }

    public function testAbsentFragmentDoesNotAddHashInToString(): void
    {
        $uri = Uri::fromString('https://example.com/path');

        static::assertStringNotContainsString('#', (string) $uri);
    }

    public function testEmptyQueryPresent(): void
    {
        // 'https://example.com/?' has an empty query (present but empty)
        $uri = Uri::fromString('https://example.com/?');

        static::assertSame('', $uri->getQuery());
        static::assertSame('https://example.com/?', (string) $uri);
    }

    // -----------------------------------------------------------------------
    // No authority: getAuthority and getHost return ''
    // -----------------------------------------------------------------------

    public function testNoAuthorityReturnsEmpty(): void
    {
        $uri = Uri::fromString('urn:isbn:0451450523');

        static::assertSame('', $uri->getAuthority());
        static::assertSame('', $uri->getHost());
        static::assertNull($uri->getPort());
        static::assertSame('', $uri->getUserInfo());
    }

    // -----------------------------------------------------------------------
    // __toString delegates to recomposition
    // -----------------------------------------------------------------------

    public function testToStringRoundTrip(): void
    {
        $cases = [
            'https://example.com/',
            'http://user@host/path?q=1#frag',
            '/just/a/path',
            '?only=query',
            'urn:isbn:0451450523',
        ];

        foreach ($cases as $raw) {
            $uri = Uri::fromString($raw);
            static::assertSame($raw, (string) $uri, "Round-trip failed for: {$raw}");
        }
    }
}
