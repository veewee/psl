<?php

declare(strict_types=1);

namespace Psl\Type\Internal;

use Psl\Type\Exception\AssertException;
use Psl\Type\Exception\CoercionException;
use Psl\Type\Type;

/**
 * @template T as object
 *
 * @extends Type<T>
 *
 * @internal
 * @psalm-immutable
 */
final class ObjectType extends Type
{
    /**
     * @psalm-var class-string<T> $classname
     */
    private string $classname;

    /**
     * @psalm-param class-string<T> $classname
     */
    public function __construct(
        string $classname
    ) {
        $this->classname = $classname;
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return T
     *
     * @throws CoercionException
     */
    public function coerce($value): object
    {
        if ($value instanceof $this->classname) {
            return $value;
        }

        throw CoercionException::withValue($value, $this->toString(), $this->getTrace());
    }

    /**
     * @psalm-param mixed $value
     *
     * @psalm-return T
     *
     * @psalm-assert T $value
     *
     * @throws AssertException
     */
    public function assert($value): object
    {
        if ($value instanceof $this->classname) {
            return $value;
        }

        throw AssertException::withValue($value, $this->toString(), $this->getTrace());
    }

    public function toString(): string
    {
        return $this->classname;
    }
}
