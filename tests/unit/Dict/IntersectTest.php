<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use PHPUnit\Framework\TestCase;
use Psl\Dict;
use Psl\Vec;

final class IntersectTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testIntersect(array $expected, iterable $first, iterable $second, iterable ...$rest): void
    {
        static::assertSame($expected, Dict\intersect($first, $second, ...$rest));
    }

    public function provideData(): iterable
    {
        yield [[], [], [], []];
        yield [[], [], [1, 2, 3]];
        yield [[], [], [1, 2, 3], []];
        yield [[], [], [1, 2, 3], [], [4, 5]];

        yield [[], [1, 2], [], []];
        yield [[], [1, 2], ['foo' => 2], []];
        yield [[], [1, 2], [], ['baz' => 1]];
        yield [[], [1, 2], ['foo' => 2], ['baz' => 1]];
        yield [[], [1, 2], ['foo' => 2], ['baz' => 1]];
        yield [[1, 2], [1, 2], ['foo' => 2, 'baz' => 1]];
        yield [[1, 2, 3, 4, 5, 6], Vec\range(1, 8), Vec\range(1, 6)];
        yield [[], Vec\range(1, 8), Vec\range(1, 6), []];
        yield [[5 => 6], Vec\range(1, 8), Vec\range(1, 6), [5 => 6, 6 => 7]];
    }
}
