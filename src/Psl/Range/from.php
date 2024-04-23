<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * @psalm-mutation-free
 *
 * @template T of int|float
 *
 * @param T $lower_bound
 *
 * @return FromRange<T>
 */
function from(int|float $lower_bound): FromRange
{
    return new FromRange($lower_bound);
}
