<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-return Type<array-key>
 * @psalm-pure
 */
function array_key(): Type
{
    return new Internal\ArrayKeyType();
}
