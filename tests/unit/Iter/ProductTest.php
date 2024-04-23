<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Vec;

final class ProductTest extends TestCase
{
    public function testProduct(): void
    {
        $result  = Iter\product(Vec\range(1, 2), Vec\range(3, 4));
        $entries = Vec\enumerate($result);

        static::assertCount(4, $entries);

        static::assertSame([0, [1, 3]], $entries[0]);
        static::assertSame([1, [1, 4]], $entries[1]);
        static::assertSame([2, [2, 3]], $entries[2]);
        static::assertSame([3, [2, 4]], $entries[3]);
    }

    public function testProductEmpty(): void
    {
        $result  = Iter\product(...[]);
        $entries = Vec\enumerate($result);

        static::assertCount(0, $entries);

        static::assertSame([], $entries);
    }
}
