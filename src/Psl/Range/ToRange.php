<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * A `ToRange` is a range that contains all values up to the upper bound.
 *
 * This range cannot serve as an Iterator because it does not have a starting point.
 *
 * @see RangeInterface::contains()
 * @see UpperBoundRangeInterface::getUpperBound()
 * @see UpperBoundRangeInterface::isUpperInclusive()
 *
 * @psalm-immutable
 *
 * @template T of int|float
 *
 * @implements UpperBoundRangeInterface<T>
 */
final readonly class ToRange implements UpperBoundRangeInterface
{
    /**
     * @var T
     */
    private int|float  $upperBound;
    private bool $upperInclusive;

    /**
     * @psalm-mutation-free
     *
     * @param T $upper_bound
     */
    public function __construct(int|float $upper_bound, bool $upper_inclusive = false)
    {
        $this->upperBound = $upper_bound;
        $this->upperInclusive = $upper_inclusive;
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
        if ($this->upperInclusive) {
            return $value <= $this->upperBound;
        }

        return $value < $this->upperBound;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\InvalidRangeException If the lower bound is greater than the upper bound.
     *
     * @psalm-mutation-free
     *
     * @param T $lower_bound
     *
     * @return BetweenRange<T>
     */
    public function withLowerBound(int|float $lower_bound): BetweenRange
    {
        return new BetweenRange(
            $lower_bound,
            $this->upperBound,
            $this->upperInclusive,
        );
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     *
     * @return FullRange<T>
     */
    public function withoutUpperBound(): FullRange
    {
        /** @var FullRange<T> */
        return new FullRange();
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
        return new self($upper_bound, $upper_inclusive);
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
        return new self($upper_bound, true);
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
        return new self($upper_bound, false);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     *
     * @return T
     */
    public function getUpperBound(): int|float
    {
        return $this->upperBound;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    public function isUpperInclusive(): bool
    {
        return $this->upperInclusive;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     *
     * @return static<T>
     */
    public function withUpperInclusive(bool $upper_inclusive): static
    {
        return new static(
            $this->upperBound,
            $upper_inclusive,
        );
    }
}
