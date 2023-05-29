[Back to Index](index.md)

# Deferring Actions

FEAST contains both a simple, callback based way and a more configurable extendable class to take deferred actions at
the end of current scope (post function return if inside function, at end of script if in global scope) by making use of
destructors.

## Deferred Callback

The `DeferredCall` class that allows you to call any "callable". The `DeferredCall` class constructor takes a callable
as the only parameter. You may cancel the Deferred handler by calling the `cancel` method of the object returned.

The following code examples (greatly simplified) show usage.

```php
protected function deferredCall(): void
{
    $deferred = new \Feast\DeferredCall(function() { echo 'This is second'; });
    echo 'This is first.';
}
```

The above code would echo `This is first. This is second.`

```php
protected function deferredCallCancelled(): void
{
    $deferred = new \Feast\DeferredCall(function() { echo ' This is second'; });
    echo 'This is first.';
    $deferred->cancelDeferral();
```

The above code would only echo `This is first.`

## Deferred Abstract class

The `Deferred` abstract class must be extended. This class has a required method `deferredAction` that must be
implemented in the child. There are no mandatory constructor arguments for this class and can be used for more dynamic
use cases.