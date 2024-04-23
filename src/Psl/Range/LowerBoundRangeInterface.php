<?php

declare(strict_types=1);

namespace Psl\Range;

use IteratorAggregate;
use Psl\Iter;
use Psl\Math;

/**
 * @template T of int|float
 *
 * @extends IteratorAggregate<int<0, max>, T>
 * @extends RangeInterface<T>
 *
 * @psalm-immutable
 */
interface LowerBoundRangeInterface extends IteratorAggregate, RangeInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws Exception\InvalidRangeException If the lower bound is greater than the upper bound.
     *
     * @psalm-mutation-free
     *
     * @param T $upper_bound
     *
     * @return UpperBoundRangeInterface<T>&LowerBoundRangeInterface<T>
     */
    public function withUpperBound(int|float $upper_bound, bool $upper_inclusive): UpperBoundRangeInterface&LowerBoundRangeInterface;

    /**
     * {@inheritDoc}
     *
     * @throws Exception\InvalidRangeException If the lower bound is greater than the upper bound.
     *
     * @psalm-mutation-free
     *
     * @param T $upper_bound
     *
     * @return UpperBoundRangeInterface<T>&LowerBoundRangeInterface<T>
     */
    public function withUpperBoundInclusive(int|float $upper_bound): UpperBoundRangeInterface&LowerBoundRangeInterface;

    /**
     * {@inheritDoc}
     *
     * @throws Exception\InvalidRangeException If the lower bound is greater than the upper bound.
     *
     * @psalm-mutation-free
     *
     * @param T $upper_bound
     *
     * @return UpperBoundRangeInterface<T>&LowerBoundRangeInterface<T>
     */
    public function withUpperBoundExclusive(int|float $upper_bound): UpperBoundRangeInterface&LowerBoundRangeInterface;

    /**
     * Remove the lower bound from the range.
     *
     * @psalm-mutation-free
     *
     * @return RangeInterface<T>
     */
    public function withoutLowerBound(): RangeInterface;

    /**
     * Returns the lower bound of the range.
     *
     * @psalm-mutation-free
     *
     * @return T
     */
    public function getLowerBound(): int|float;

    /**
     * Returns an iterator for the range.
     *
     * If this range has no upper bound, the iterator will be infinite.
     *
     * If {@see Math\INT64_MAX} of {@see Math\FLOAT64_MAX} is reached while iterating, {@see Exception\OverflowException} will be thrown.
     *
     * @psalm-mutation-free
     *
     * @return Iter\Iterator<int<0, max>, T>
     */
    public function getIterator(): Iter\Iterator;
}
