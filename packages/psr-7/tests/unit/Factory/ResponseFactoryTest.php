<?php

declare(strict_types=1);

namespace Psl\Psr\Http\Tests\Unit\Factory;

use PHPUnit\Framework\TestCase;
use Psl\Psr\Http\Factory\ResponseFactory;
use Psr\Http\Message\ResponseInterface;

final class ResponseFactoryTest extends TestCase
{
    public function testCreateResponseDefault(): void
    {
        $factory = new ResponseFactory();
        $response = $factory->createResponse();

        static::assertInstanceOf(ResponseInterface::class, $response);
        static::assertSame(200, $response->getStatusCode());
        static::assertSame('OK', $response->getReasonPhrase());
    }

    public function testCreateResponseWithStatusCode(): void
    {
        $factory = new ResponseFactory();
        $response = $factory->createResponse(404);

        static::assertSame(404, $response->getStatusCode());
        static::assertSame('Not Found', $response->getReasonPhrase());
    }

    public function testCreateResponseWithCustomReasonPhrase(): void
    {
        $factory = new ResponseFactory();
        $response = $factory->createResponse(200, 'Custom Phrase');

        static::assertSame('Custom Phrase', $response->getReasonPhrase());
    }

    public function testCreateResponseHasEmptyBody(): void
    {
        $factory = new ResponseFactory();
        $response = $factory->createResponse();

        static::assertSame('', $response->getBody()->getContents());
    }
}
