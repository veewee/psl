<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * a range is a set of values that are contained in the range.
 *
 * @template T of int|float
 *
 * @psalm-immutable
 */
interface RangeInterface
{
    /**
     * Checks if the given value is contained in the range.
     *
     * @psalm-mutation-free
     *
     * @param T $value
     */
    public function contains(int|float $value): bool;

    /**
     * Combine this range with the given lower bound.
     *
     * @psalm-mutation-free
     *
     * @param T $lower_bound
     *
     * @return LowerBoundRangeInterface<T>
     */
    public function withLowerBound(int|float $lower_bound): LowerBoundRangeInterface;

    /**
     * Combine this range with the given upper bound.
     *
     * @psalm-mutation-free
     *
     * @param T $upper_bound
     *
     * @return UpperBoundRangeInterface<T>
     */
    public function withUpperBound(int|float $upper_bound, bool $upper_inclusive): UpperBoundRangeInterface;

    /**
     * Combine this range with the given upper bound, and make it inclusive.
     *
     * @psalm-mutation-free
     *
     * @param T $upper_bound
     *
     * @return UpperBoundRangeInterface<T>
     */
    public function withUpperBoundInclusive(int|float $upper_bound): UpperBoundRangeInterface;

    /**
     * Combine this range with the given upper bound, and make it exclusive.
     *
     * @psalm-mutation-free
     *
     * @param T $upper_bound
     *
     * @return UpperBoundRangeInterface<T>
     */
    public function withUpperBoundExclusive(int|float $upper_bound): UpperBoundRangeInterface;
}
