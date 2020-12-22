# Result\ResultInterface

## Introduction

The `ResultInterface` is the common ground for both the `Success` and `Failure`classes.

## Class Synopsis
```
interface ResultInterface
{
    public function getResult();
    public function getException(): Exception;
    public function isSucceeded(): bool;
    public function isFailed(): bool;
    public function proceed(callable $on_success, callable $on_failure);
    public function then(callable $on_success, callable $on_failure): ResultInterface;
}
```

## Methods

### getResult()

