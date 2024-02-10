---
collections:
  - tutorials
layout: dm:tutorial
parent: tutorials/index
tags:
    - GraphQL
title: Building a Basic GraphQL Schema
description: >
    Learn How to Build a Basic GraphQL Schema
---

## What is GraphQL?

GraphQL is an alternative to REST and other API systems that, in essence, convert your application into a graph database that you can query against.

Instead of exposing and hand-crafting the specific endpoints, you can expose the data structures instead.

This approach empowers API consumers like front-end applications and makes it unnecessary to introduce constant changes in the back-end of an application.

For example, let's say you are querying the schema for a few fields:

```graphql
query BlogPosts() {
    post1: blogPost(slug: "foo") { content }
    post2: blogPost(slug: "bar") { content }
}
```

Resonance will internally make two database queries in parallel (one for the "foo" blog post and the second one for the "bar" blog post) - similarly to how it would happen with Node.js, Go, or other inherently asynchronous languages.

## Basic GraphQL Schema

Let's start with a basic GraphQL schema - one that exposes the `ping` object that has a single field: `message` which always resolves to `"pong"`.

For example, the query:

```graphql
query Ping() {
    ping1: ping { message }
    ping2: ping { message } 
    ping3: ping { message }
}
```

Is going to return the following JSON:

```json
{
    "data": {
        "ping1": { "message": "pong" },
        "ping2": { "message": "pong" },
        "ping3": { "message": "pong" }
    }
}
```

### Exposing the GraphQL Endpoint

Let's create a {{docs/features/http/responders}} which handles all the `POST` requests issued to the '/graphql' and returns GraphQL responses:

```php
<?php

namespace App\HttpResponder;

use App\HttpRouteSymbol;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpResponder\GraphQL as ResonanceGraphQL;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\RequestMethod;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SiteAction;
use Swoole\Http\Request;
use Swoole\Http\Response;

#[RespondsToHttp(
    method: RequestMethod::POST,
    pattern: '/graphql',
    routeSymbol: HttpRouteSymbol::GraphQL,
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
final readonly class GraphQL extends HttpResponder
{
    public function __construct(private ResonanceGraphQL $graphql) {}

    public function respond(Request $request, Response $response): HttpResponderInterface
    {
        return $this->graphql;
    }
}
```

### The Ping Object Type

Resonance's {{docs/features/graphql/index}} implementation uses the [webonyx/graphql-php](https://webonyx.github.io/graphql-php/) library internally to build schemas so that we will follow their object type format.

We will use a `Closure` to resolve the `ping` field value:

```php
<?php

namespace App\ObjectType;

use Distantmagic\Resonance\Attribute\Singleton;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

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
                    'resolve' => $this->resolvePong(...),
                ],
            ],
        ]);
    }

    private function resolvePong(string $rootValue): string
    {
        return $rootValue;
    }
}
```

### Building the Root Query

Now that we have a `ping` type, we need to assign it to the root GraphQL schema so you can query it.

Just defining the `ping` type did nothing yet because GraphQL still doesn't know the name of the field and other necessary details to use that field.

Let's define those now:

```php
<?php

namespace App\GraphQLRootField;

use App\ObjectType\PingType;
use Distantmagic\Resonance\Attribute\GraphQLRootField;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\GraphQLFieldableInterface;
use Distantmagic\Resonance\GraphQLRootFieldType;
use Distantmagic\Resonance\SingletonCollection;

#[GraphQLRootField(
    name: 'ping',
    type: GraphQLRootFieldType::Query,
)]
#[Singleton(collection: SingletonCollection::GraphQLRootField)]
final readonly class Ping implements GraphQLFieldableInterface
{
    public function __construct(private PingType $pingType) {}

    public function resolve(): string
    {
        return 'pong';
    }

    public function toGraphQLField(): array
    {
        return [
            'type' => $this->pingType,
            'resolve' => $this->resolve(...),
        ];
    }
}
```

That is it! Now, your project should have the GraphQL schema exposed at the `/graphql` URL.

## Summary

This tutorial explains how to start with a GraphQL schema with Resonance. If you want to expand this schema, add more Object Types and nest them inside each other. I encourage you to experiment and build a more complex graph.

