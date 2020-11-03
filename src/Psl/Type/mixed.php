<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-return Type<mixed>
 * @psalm-pure
 */
function mixed(): Type
{
    return new Internal\MixedType();
}
