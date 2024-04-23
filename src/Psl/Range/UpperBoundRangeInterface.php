<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * @psalm-immutable
 *
 * @template T of int|float
 * @extends RangeInterface<T>
 */
interface UpperBoundRangeInterface extends RangeInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws Exception\InvalidRangeException If the lower bound is greater than the upper bound.
     *
     * @psalm-mutation-free
     *
     * @param T $lower_bound
     *
     * @return UpperBoundRangeInterface<T>&LowerBoundRangeInterface<T>
     */
    public function withLowerBound(int|float $lower_bound): UpperBoundRangeInterface&LowerBoundRangeInterface;

    /**
     * Remove the upper bound from the range.
     *
     * @psalm-mutation-free
     *
     * @return RangeInterface<T>
     */
    public function withoutUpperBound(): RangeInterface;

    /**
     * Returns the upper bound of the range.
     *
     * @psalm-mutation-free
     *
     * @return T
     */
    public function getUpperBound(): int|float;

    /**
     * Returns whether the upper bound is inclusive.
     *
     * @psalm-mutation-free
     */
    public function isUpperInclusive(): bool;

    /**
     * Return a new instance, where the upper bound inclusive flag is set to the given value.
     *
     * @psalm-mutation-free
     *
     * @return static<T>
     */
    public function withUpperInclusive(bool $upper_inclusive): static;
}
