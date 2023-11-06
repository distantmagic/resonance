---
collections: 
    - documents
layout: dm:document
parent: docs/features/graphql/index
title: Standalone Promise Adapter
description: >
    Use Resonance's Swoole Promise adapter as a standalone component.
---

# Standalone Promise Adapter


This adapter wraps every GraphQL field resolver in a coroutine.

# Installation

:::note
If you are using Resonance framework you don't have to additionally install the
GraphQL adapter - it's already bundled.

You need this step only if you want to use just the Swoole adapter - without
the remaining Resonance features.
:::

Swoole Promise Adapter ius bundled with the base Resonance framework, but if 
you want to use it standalone, without other Resonance features, you can use:

```shell
$ composer require distantmagic/graphql-swoole-promise-adapter
```

# Usage

You need to pass it as an adapter argument to GraphQL facade provided by
[`webonyx/graphql-php`](https://github.com/webonyx/graphql-php).

After that all queries will use Swoole's coroutines and will be resolved 
asynchronously:

```php
<?php

use Distantmagic\GraphqlSwoolePromiseAdapter\GraphqlSwoolePromiseAdapter;
use GraphQL\GraphQL as GraphQLFacade;
use GraphQL\Type\Schema;

/**
 * @var Schema $schema
 * @var string $query
 * @var mixed $rootValue
 * @var mixed $context
 * @var null|array<string,mixed> $variableValues
 */
$promise = GraphQLFacade::promiseToExecute(
    new GraphqlSwoolePromiseAdapter(),
    $schema,
    $query,
    $rootValue,
    $context,
    $variableValues,
);

$promise->then(function (mixed $result) {
    // ...
});
```
