<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-return Type<null>
 * @psalm-pure
 */
function null(): Type
{
    return new Internal\NullType();
}
