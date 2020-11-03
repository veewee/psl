<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-return Type<int>
 * @psalm-pure
 */
function int(): Type
{
    return new Internal\IntType();
}
