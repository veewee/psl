<?php

use Psl\Vec;
use function Psl\Fun\partial_right;


/**
 * @return Closure(list<int>): list<non-empty-lowercase-string>
 */
function test_vec_map(): \Closure
{
    return partial_right(Vec\map(...), function (int $x): string {
        return "".$x;
    });
}
