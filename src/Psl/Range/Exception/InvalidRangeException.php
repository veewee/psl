<?php

declare(strict_types=1);

namespace Psl\Range\Exception;

use Psl\Exception;
use Psl\Str;

/**
 * @template T of int|float
 */
final class InvalidRangeException extends Exception\InvalidArgumentException implements ExceptionInterface
{
    /**
     * @param T $lower_bound
     * @param T $upper_bound
     */
    public function __construct(
        string $message,
        private readonly int|float $lower_bound,
        private readonly int|float $upper_bound,
    ) {
        parent::__construct($message);
    }

    /**
     * @template Tv of int|float
     *
     * @param Tv $lower_bound
     * @param Tv $upper_bound
     *
     * @return self<Tv>
     */
    public static function lowerBoundIsGreaterThanUpperBound(
        int|float $lower_bound,
        int|float $upper_bound,
    ): self {
        return new self(
            Str\format(
                '`$lower_bound` (%d) must be less than or equal to `$upper_bound` (%d).',
                $lower_bound,
                $upper_bound,
            ),
            $lower_bound,
            $upper_bound,
        );
    }

    /**
     * Returns the lower bound.
     *
     * @return T
     */
    public function getLowerBound(): int|float
    {
        return $this->lower_bound;
    }

    /**
     * Returns the upper bound.
     *
     * @return T
     */
    public function getUpperBound(): int|float
    {
        return $this->upper_bound;
    }
}
