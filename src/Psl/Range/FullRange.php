<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * A `FullRange` is a range that contains all values.
 *
 * This range cannot serve as an Iterator because it does not have a starting point.
 *
 * @see RangeInterface::contains()
 *
 * @psalm-immutable
 *
 * @template T of int|float
 *
 * @implements RangeInterface<T>
 */
final class FullRange implements RangeInterface
{
    /**
     * This function always returns true.
     *
     * @psalm-mutation-free
     *
     * @return true
     *
     * @param T $value
     */
    public function contains(int|float $value): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     *
     * @param T $lower_bound
     *
     * @return FromRange<T>
     */
    public function withLowerBound(int|float $lower_bound): FromRange
    {
        return new FromRange(
            $lower_bound,
        );
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     *
     * @param T $upper_bound
     *
     * @return ToRange<T>
     */
    public function withUpperBound(int|float $upper_bound, bool $upper_inclusive): ToRange
    {
        return new ToRange($upper_bound, $upper_inclusive);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     *
     * @param T $upper_bound
     *
     * @return ToRange<T>
     */
    public function withUpperBoundInclusive(int|float $upper_bound): ToRange
    {
        return new ToRange($upper_bound, true);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     *
     * @param T $upper_bound
     *
     * @return ToRange<T>
     */
    public function withUpperBoundExclusive(int|float $upper_bound): ToRange
    {
        return new ToRange($upper_bound, false);
    }
}
