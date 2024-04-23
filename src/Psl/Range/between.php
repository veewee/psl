<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * @throws Exception\InvalidRangeException If the lower bound is greater than the upper bound.
 *
 * @psalm-mutation-free
 *
 * @template T of int|float
 *
 * @param T $lower_bound
 * @param T $upper_bound
 *
 * @return BetweenRange<T>
 */
function between(int|float $lower_bound, int|float $upper_bound, bool $upper_inclusive = false): BetweenRange
{
    return new BetweenRange($lower_bound, $upper_bound, $upper_inclusive);
}
