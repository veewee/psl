# Result

The Result component can be used to wrap either a value or exception.
You can pass the result around and run actions on it until you decide to unwrap the value or exception.

## Examples

```php
use Psl\Fun;
use Psl\Result;
use Psl\SecureRandom;

// Create and process result:
$result = Result\wrap(
    static function (): string {
        if (SecureRandom\int(0,100) > 70) {
            throw new Exception('Sometimes this function fails.');
        }
        
        return 'World';
    }
)->then(
    static fn (string $name): string => 'Hello ' . $name . '!',
    Fun\rethrow()
);

// Unwrap the result with type-safe fallback:
$greeting = $result->proceed(
    static fn (string $greeting): string => $greeting,
    static fn (Exception $error): string => 'Hello there!',
);

// Validate result and get the actual result value:
if ($result->isSucceeded()) {
    echo $result->getResult();
}
```

A result is compatible with reactPHP's promises, meaning you can mix it in an async context as well.
It has been tested with both amphp and ReactPHP. 


## Functions

- [`Psl\Result\wrap`](function/wrap.md)

## Interfaces

- [`Psl\Result\ResultInterface`](interface/ResultInterface.md)

## Classes

- [`Psl\Result\Failure`](class/Failure.md)
- [`Psl\Result\Success`](class/Success.md)
