# PSL PSR-7

A client-side PSR-7 / PSR-17 implementation built on PSL.

It provides the PSR-7 message types `Uri`, `Request`, and `Response`, a `Stream` that wraps any
`Psl\IO` handle (plus a reverse `StreamReadHandle` that exposes a PSR-7 stream as a
`Psl\IO\ReadHandleInterface`), and the matching PSR-17 factories (`UriFactory`, `RequestFactory`,
`ResponseFactory`, `StreamFactory`). Headers reuse `Psl\HTTP\Message\FieldMap`, URIs reuse `Psl\URI`,
and stream bodies stay on `Psl\IO`.

Server-side types (`ServerRequest`, `UploadedFile`) are intentionally out of scope; PSL's HTTP model
is async and streaming rather than SAPI based, so server interop belongs in a separate bridge.

See the [main repository](https://github.com/php-standard-library/php-standard-library).
