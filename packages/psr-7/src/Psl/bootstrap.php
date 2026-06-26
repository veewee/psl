<?php

declare(strict_types=1);

(static function (): void {
    $functions = [];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
