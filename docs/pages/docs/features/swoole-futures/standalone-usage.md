---
collections: 
    - documents
layout: dm:document
parent: docs/features/swoole-futures/index
title: Standalone Usage
description: >
    Learn how to use Swoole Futures on their own, without any Resonance 
    components.
---

# Standalone Usage

While Swoole Futures are primarily designed for use within GraphQL resolvers 
or event listeners, you can also use them independently. You don't need any 
additional wrappers (besides the Swoole's event loop, of course), as 
`SwooleFuture` is a primitive type within the Resonance framework. Here's an 
example of standalone usage:

```php
<?php

use Distantmagic\SwooleFuture\SwooleFuture;

use function Swoole\Coroutine\run;

// `run` starts Swoole's event loop
// 
// it's not necessary to call this function inside HTTP Resolvers, 
// GraphQL context nor WebSocket context - all messaging within the framework
// is already done in the event loop
run(function () {
    $future = new SwooleFuture(function (int $value) {
        sleep(1);

        return 1 + $value;
    });

    // SwooleFuture is asynchronous, but you can immediately use the result.
    // They work similarily to PHP Fibers (interrupt the code execution and
    // come back later to the same stack when async operation is done):
    assert(6 === $future->resolve(2)->result + 3);
});
```

:::caution
`SwooleFuture` can only be resolved once. 

Attempting to resolve it multiple times will result in an exception.

```php
<?php

$swooleFuture->resolve();
$swooleFuture->resolve(); // throws logic exception
```
:::

## About Thenables

`SwooleFuture` is a "thenable" object that supports chaining. 
Chained objects are executed sequentially in relation to each other but 
still asynchronously:

```php
<?php

$future1 = new SwooleFuture(function (int $value) {
    assert($value === 1);

    return $value + 2;
});

$future2 = $future1->then(new SwooleFuture(function (int $value) {
    assert($value === 3);

    return $value + 4;
}));

assert($future2->resolve(1)->result === 7);
```

:::
The term "thenable" originates from the [Promise spec](https://promisesaplus.com/)
:::

## Catching Errors

When using ' then ', you can specify callbacks for both success and failure 
scenarios. That allows you to handle errors gracefully:

```php
<?php

SwooleFuture::create(function () {
    throw new Exception(':(');
})->then(
    new SwooleFuture(function () {
        // This callback is not going to be executed.
        return 1;
    }),
    new SwooleFuture(function (Exception $exception) {
        var_dump($exception->getMessage()); // ":("

        // recovering
        return 2;
    })
)->then(
    new SwooleFuture (int $value) {
        var_dump($value); // 2
    },
    new SwooleFuture(function () {
        // This callback is not going to be executed since 
        // the error has been recovered in the previous step.
    }),
);
```

That allows you to recover from errors during asynchronous gracefully 
operations.
