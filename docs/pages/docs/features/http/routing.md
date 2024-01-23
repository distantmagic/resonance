---
collections: 
    - documents
layout: dm:document
parent: docs/features/http/index
title: Routing
description: >
    Handle HTTP routes with attributes.
---

# HTTP Routing

# Usage

## Defining Routes

Each HTTP Responder corresponds to a specific route, and you define these 
routes using the `RespondsToHttp` attribute. They must also be registered
as singletons in the {{docs/features/dependency-injection/index}} container:

```php
<?php

use App\HttpRouteSymbol;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\RequestMethod;
use Distantmagic\Resonance\SingletonCollection;

#[RespondsToHttp(
    method: RequestMethod::GET,
    pattern: '/my_responder',
    routeSymbol: HttpRouteSymbol::MyResponder,
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
final readonly class MyResponder implements HttpResponderInterface
{
    // (...)
}
```

`routeSymbol` is a symbolic route name which you can later use in templates or 
link builders.

:::tip
To maintain consistency in your application, consider organizing route symbols 
using an enum:

```php
<?php

enum HttpRouteSymbol
{
    case Homepage;
    case Blog;
}

// --------------

use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpResponderInterface;

#[RespondsToHttp(
    method: RequestMethod::GET,
    pattern: '/',
    routeSymbol: HttpRouteSymbol::Homepage,
)]
#[Singleton]
class MyResponder implements HttpResponderInterface
{
    // (...)
}
```
:::


## Route Parameter Constraints

You can attach additional regular expression constraints to route parameters.
They change how the framework matches route parameters. For example, this route 
is going to match URLs like `/public/favicon.ico` or `/public/images/foo.jpg`:

```php
#[RespondsToHttp(
    method: RequestMethod::GET,
    pattern: '/public/{asset}',
    requirements: [
        'asset' => '[\-\.\w\/]+',
    ],
    routeSymbol: HttpRouteSymbol::Asset,
)]
```
