---
collections: 
    - documents
layout: dm:document
parent: docs/features/index
title: Swoole Futures
description: >
    Use Swoole Futures for asynchronous operations in event dispatchers, 
    GraphQL, and more. Partial Promises/A+ implementation in PHP.
---

# Swoole Futures

Swoole Futures serve as primitive values in Resonance for asynchronous value 
resolution. They enable asynchronous operations, such as parallel GraphQL 
field resolution.

:::note
Swoole Futures provide a *partial* implementation of 
[Promise spec](https://promisesaplus.com/).
:::

# Usage

## EventDispatcher

The event dispatcher uses Swoole Futures internally to make event 
listeners asynchronous. You can find more information on the 
{{docs/features/events/index}} documentation.

## GraphQL Resolvers

When working with GraphQL resolvers, it's a good practice to wrap asynchronous 
operations like database queries or API requests in a `SwooleFuture`:

```php
<?php

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Distantmagic\Resonance\Framework\Attribute\Singleton;
use Distantmagic\Resonance\Framework\SwooleFuture;

#[Singleton]
final class PingType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'Ping',
            'description' => 'Test field',
            'fields' => [
                'message' => [
                    'type' => Type::string(),
                    'description' => 'Always responds with pong',
                    'resolve' => $this->resolveMessage(...),
                ],
            ],
        ]);
    }

    private function resolveMessage(): SwooleFuture
    {
        return new SwooleFuture(function () {
            sleep(1);

            return 'pong';
        });
    }
}
```

By using `SwooleFuture`, you ensure the framework executes resolve callbacks in 
parallel. That significantly improves performance, especially when multiple 
resolvers are involved.

For example, the following GraphQL query will take around 1 second (instead of 
3 seconds) because resolvers are executed in parallel. 

```graphql
query MultiPing() {
    ping1: ping { message }
    ping2: ping { message }
    ping3: ping { message }
}
```

### GraphQL Resolver Convenience Wrapper

If you prefer a cleaner code structure, you can use the convenience wrapper 
`SwooleFuture::resolver` to wrap your entire resolver callback:

```php
<?php

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Distantmagic\Resonance\Framework\Attribute\Singleton;
use Distantmagic\Resonance\Framework\SwooleFuture;

// (...)

    'resolve' => SwooleFuture::resolver($this->resolveMessage(...)),

// (...)

    private function resolveMessage(): SwooleFuture
    {
        sleep(1);

        return 'pong';
    }

// (...)
```

That achieves the same result but with a more concise syntax.

## Standalone

While Swoole Futures are primarily designed for use within GraphQL resolvers 
or event listeners, you can also use them independently. You don't need any 
additional wrappers (besides the Swoole's event loop, of course), as 
`SwooleFuture` is a primitive type within the Resonance framework. Here's an 
example of standalone usage:

```php
<?php

use Distantmagic\Resonance\Framework\SwooleFuture;

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
