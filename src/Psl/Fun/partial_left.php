<?php

declare(strict_types=1);

namespace Psl\Fun;

use Closure;

/**
 * Create a partial closure that contains arguments appended at the left side of the function
 *
 * @return Closure
 * @no-named-arguments
 *
 * @pure
 */
function partial_left(callable $fn, mixed ... $default_arguments) {


    /*
     *   if ($arity !== null) {
    $args = array_pad($args, $arity, null);
    $args = array_slice($args, 0, $arity);
  }
     */

    return
        /**
         * @no-named-arguments
         */
        static function(mixed ... $rest_arguments) use ($fn, $default_arguments) {
            $full_arguments = array_merge($default_arguments, $rest_arguments);
            return $fn(...$full_arguments);
        };
}
