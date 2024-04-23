<?php

declare(strict_types=1);

namespace Psl\Range;

use Generator;
use Psl\Iter;
use Psl\Math;

/**
 * A `FromRange` is a range that contains all values greater than or equal to the given lower bound.
 *
 * This range can serve as an Iterator, starting from the lower bound.
 *
 * ```php
 * use Psl\Range;
 *
 * $range = new Range\FromRange(1);
 *
 * foreach ($range as $value) {
 *    // $value will be 1, 2, 3, 4, 5, ...
 * }
 * ```
 *
 * Iterating over this range is not recommended, as it is an infinite range.
 *
 * @see RangeInterface::contains()
 * @see LowerBoundRangeInterface::getLowerBound()
 *
 * @psalm-immutable
 *
 * @template T of int|float
 *
 * @implements LowerBoundRangeInterface<T>
 */
final readonly class FromRange implements LowerBoundRangeInterface
{
    /**
     * @var T
     */
    private int|float $lowerBound;

    /**
     * @psalm-mutation-free
     *
     * @param T $lower_bound
     */
    public function __construct(int|float $lower_bound)
    {
        $this->lowerBound = $lower_bound;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     *
     * @param T $value
     */
    public function contains(int|float $value): bool
    {
        return $value >= $this->lowerBound;
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
     * @throws Exception\InvalidRangeException If the lower bound is greater than the upper bound.
     *
     * @psalm-mutation-free
     *
     * @param T $upper_bound
     *
     * @return BetweenRange<T>
     */
    public function withUpperBound(int|float $upper_bound, bool $upper_inclusive): BetweenRange
    {
        return new BetweenRange(
            $this->lowerBound,
            $upper_bound,
            $upper_inclusive,
        );
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\InvalidRangeException If the lower bound is greater than the upper bound.
     *
     * @psalm-mutation-free
     *
     * @param T $upper_bound
     *
     * @return BetweenRange<T>
     */
    public function withUpperBoundInclusive(int|float $upper_bound): BetweenRange
    {
        return new BetweenRange(
            $this->lowerBound,
            $upper_bound,
            true,
        );
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\InvalidRangeException If the lower bound is greater than the upper bound.
     *
     * @psalm-mutation-free
     *
     * @param T $upper_bound
     *
     * @return BetweenRange<T>
     */
    public function withUpperBoundExclusive(int|float $upper_bound): BetweenRange
    {
        return new BetweenRange(
            $this->lowerBound,
            $upper_bound,
            false,
        );
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     *
     * @return FullRange<T>
     */
    public function withoutLowerBound(): FullRange
    {
        /** @var FullRange<T> */
        return new FullRange();
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     *
     * @return T
     */
    public function getLowerBound(): int|float
    {
        return $this->lowerBound;
    }

    /**
     * {@inheritDoc}
     *
     * @return Iter\Iterator<int<0, max>, T>
     *
     * @psalm-mutation-free
     *
     * @psalm-suppress ImpureMethodCall
     */
    public function getIterator(): Iter\Iterator
    {
        $bound = $this->lowerBound;
        $isInt = is_int($this->lowerBound);

        /** @var Iter\Iterator<int<0, max>, T> */
        return Iter\Iterator::from(static function () use ($bound, $isInt): Generator {
            $value = $bound;
            while (true) {
                yield $value;

                if ($isInt && $value === Math\INT64_MAX) {
                    throw Exception\OverflowException::whileIterating($bound);
                }

                if (!$isInt && $value === Math\FLOAT64_MAX) {
                    throw Exception\OverflowException::whileIterating($bound);
                }

                $value += 1;
            }
        });
    }
}
