<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-return Type<float>
 * @psalm-pure
 */
function float(): Type
{
    return new Internal\FloatType();
}
