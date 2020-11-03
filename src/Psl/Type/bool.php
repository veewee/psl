<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-return Type<bool>
 * @psalm-pure
 */
function bool(): Type
{
    return new Internal\BoolType();
}
