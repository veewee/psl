<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * @psalm-mutation-free
 *
 * @template T of int|float
 *
 * @return FullRange<T>
 */
function full(): FullRange
{
    /** @var FullRange<T> */
    return new FullRange();
}
