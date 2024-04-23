<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Dict;
use Psl\Vec;

/**
 * Returns the cartesian product of iterables that were passed as arguments.
 *
 * The resulting iterator will contain all the possible tuples of the values.
 *
 * Examples:
 *
 *      Iter\product(
 *          Iter\range(1, 2),
 *          Iter\range(3, 4)
 *      )
 *     => Iter([1, 3], [1, 4], [2, 3], [2, 4])
 *
 * @template Tv
 *
 * @param iterable<array-key, Tv> ...$iterables Iterables to combine
 *
 * @return iterable<int<0, max>, list<Tv>>
 * @psalm-return \Generator<int, list<(Tv)|null>, mixed, void>
 */
function product(iterable ...$iterables): iterable
{
    $iterators = Vec\values(Dict\map(
        $iterables,
        static fn (iterable $iterable) => Iterator::create($iterable)
    ));

    $numIterators = count($iterators);
    if (0 === $numIterators) {
        return;
    }

    /** @var list<Tv|null> $valueTuple */
    $valueTuple = Vec\fill($numIterators, null);
    $i          = -1;

    while (true) {
        while (++$i < $numIterators - 1) {
            $iterators[$i]->rewind();
            if (!$iterators[$i]->valid()) {
                return;
            }

            $valueTuple[$i] = $iterators[$i]->current();
        }

        foreach ($iterators[$i] as $valueTuple[$i]) {
            yield Vec\values($valueTuple);
        }

        while (--$i >= 0) {
            $iterators[$i]->next();
            if ($iterators[$i]->valid()) {
                $valueTuple[$i] = $iterators[$i]->current();
                continue 2;
            }
        }

        return;
    }
}
