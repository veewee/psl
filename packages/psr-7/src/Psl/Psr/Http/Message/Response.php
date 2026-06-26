<?php

declare(strict_types=1);

namespace Psl\Psr\Http\Message;

use InvalidArgumentException;
use Psl\HTTP\Message;
use Psl\HTTP\Message\FieldMap;
use Psl\IO\MemoryHandle;
use Psl\Psr\Http\Message\Internal\MessageTrait;
use Psr\Http\Message\ResponseInterface;

final class Response implements ResponseInterface
{
    use MessageTrait;

    public function __construct(
        private int $statusCode = 200,
        private string $reasonPhrase = '',
        string $protocolVersion = '1.1',
    ) {
        $this->assertValidStatusCode($statusCode);
        $this->reasonPhrase = $reasonPhrase !== '' ? $reasonPhrase : Message\reason_phrase($statusCode);
        $this->protocolVersion = $protocolVersion;
        $this->fieldMap = FieldMap::default();
        $this->body = Stream::fromHandle(new MemoryHandle(''), 0);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    public function withStatus(int $code, string $reasonPhrase = ''): static
    {
        $this->assertValidStatusCode($code);
        $new = clone $this;
        $new->statusCode = $code;
        $new->reasonPhrase = $reasonPhrase !== '' ? $reasonPhrase : Message\reason_phrase($code);

        return $new;
    }

    /**
     * @throws InvalidArgumentException If $code is outside [100, 599].
     */
    private function assertValidStatusCode(int $code): void
    {
        if ($code < 100 || $code > 599) {
            throw new InvalidArgumentException(
                'HTTP status code must be an integer between 100 and 599; got ' . $code . '.',
            );
        }
    }
}
