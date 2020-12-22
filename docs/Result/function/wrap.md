# Result\wrap()

Wrap the output of a callback into a Result.
If the callback throw an exception, it will create a Failure result.
On success, it will create a Succeeded result.

## Description

```hack
wrap(callable(): T): Result\Success<T> | Result\Failure<T, TException>
```

## Parameters

- **$fun**
  
    The callback function that will be executed and wrapped into a result.


## Return values

Always returns a `Result\ResultInterface`:

- If the `$fun` callable returns a value, it will wrap that value into a `Result\Success($value)`.
- If the `$fun` callable throws an exception, it will wrap that value into a `Result\Failure($exception)`.

## Examples

```php
use Psl\Result;

$result = Result\wrap(
    static function (): string {
        if (SecureRandom\int(0,100) > 70) {
            throw new Exception('Sometimes this function fails.');
        }
        
        return 'World';
    }
);
```

The above example will wrap the word `World` in a `Success` result with a probability of 70%
The other 30% will result in a `Failure` result that wraps the exception.
