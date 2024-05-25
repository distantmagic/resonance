---
collections: 
    - documents
layout: dm:document
parent: docs/features/http/index
title: Responders
description: >
    Handle HTTP requests with asynchronous HTTP Responders. 
---

# HTTP Responders

HTTP Responders offer a flexible way to handle incoming HTTP requests. They are 
asynchronous and provide low-level access to HTTP headers, request content, 
cookies, connection management, and more.

This framework relies on HTTP Responders for constructing internal features, 
such as {{docs/features/graphql/index}} integration.

:::tip
If you're working on a CRUD-based application (involving operations like 
creating and updating database entities), consider using 
{{docs/features/http/controllers}}. 

It's a more suitable choice for such scenarios while employing `HttpResponder` 
can be more tedious.
:::

# Usage

## Writing Responders

HTTP Responder should be a `readonly` class that serves a single purpose 
(responds only to one specific HTTP route). 

Responders must implement the `respond` method that sends the response back to 
the HTTP client or forwards the request to a different responder.

## Responding to HTTP Requests

To respond to requests you should use Swoole's `Response` object:

```php
<?php

namespace App\HttpResponder;

use App\HttpRouteSymbol;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\RequestMethod;
use Distantmagic\Resonance\SingletonCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[RespondsToHttp(
    method: RequestMethod::GET,
    pattern: '/',
    routeSymbol: HttpRouteSymbol::Homepage,
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
final readonly class Homepage implements HttpResponderInterface
{
    public function respond(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $response->withBody($this->createStream('Hello, world!'));
    }
}
```

## Forwarding Requests to Other Responders

HTTP Responders can forward requests to other responders by returning another 
responder from the `respond` method.

For example:

```php
<?php

use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\HttpResponder\Redirect;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MyResponder implements HttpResponderInterface
{
    // (...)

    public function respond(ServerRequestInterface $request, ResponseInterface $response): HttpResponderInterface
    {
        return new Redirect('/blog');
    }
}
```

You can even use anonymous classes:

```php
<?php

use Distantmagic\Resonance\HttpResponderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MyResponder implements HttpResponderInterface
{
    public function respond(Request $request, Response $response): ?HttpResponderInterface
    {
        return new class implements HttpResponderInterface {
            public function respond (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
                return $response->withBody($this->createStream('Hello!'));
            }
        };
    }
}
```

## Function Responders

You can simplify responders even more by using function responders. 

```php
<?php

namespace App\HttpResponder;

use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\RequestMethod;
use Distantmagic\Resonance\TwigTemplate;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[RespondsToHttp(
    method: RequestMethod::GET,
    pattern: '/blog',
)]
function Blog(
    ServerRequestInterface $request,
    ResponseInterface $response,
): TwigTemplate
{
    return new TwigTemplate($request, $response, 'turbo/blog.twig');
}
```

## Built-In Responders

All of those responders use the `Distantmagic\Resonance\HttpResponder` 
namespace. For example you can use:
`Distantmagic\Resonance\HttpResponder\Error\PageNotFound`.

name | description
-|-
`GraphQL` | If you point a route to that responder, you will add {{docs/features/graphql/index}} support to your application.
`Redirect` | Returning `new Redirect('/url');` from the responder is going to produce the HTTP Redirect response.
`NotAcceptable` | Produces `406 Not Acceptable` response
`Error\BadRequest` | Produces `400 Bad Request` response
`Error\Forbidden` | Produces `403 Forbidden` response
`Error\MethodNotAlowed` | Produces `405 Method Not Allowed` response
`Error\PageNotFound` | Produces `404 Page Not Found` response
`Error\ServerError` | Produces `500 Server Error` response
