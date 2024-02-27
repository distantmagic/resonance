---
collections: 
    - documents
layout: dm:document
parent: docs/features/openapi/index
title: Exposing Schema
description: >
    Learn how to expose OpenAPI schema
---

# Exposing Schema

`OpenAPISchemaBuilder` will fetch all your project's metadata to build the 
schema, so it's best to create the response in the constructor and then 
reuse it.

# Usage

## Exposing Schema

You can expose OpenAPI schema by returning a built schema from any of your
{{docs/features/http/responders}}:

```php
<?php

namespace App\HttpResponder;

use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpResponder\Json;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\OpenAPISchemaBuilder;
use Distantmagic\Resonance\OpenAPISchemaSymbol;
use Distantmagic\Resonance\RequestMethod;
use Distantmagic\Resonance\SingletonCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[RespondsToHttp(
    method: RequestMethod::GET,
    pattern: 'openapi/schema.json',
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
readonly class OpenAPISchema implements HttpResponderInterface
{
    public Json $schema;

    public function __construct(OpenAPISchemaBuilder $openAPISchemaBuilder) 
    {
        $this->schema = $openAPISchemaBuilder->toJsonResponse(OpenAPISchemaSymbol::All);
    }

    public function respond(ServerRequestInterface $request, ResponseInterface $response): HttpResponderInterface
    {
        return $this->schema;
    }
}
```

## Choosing What Endpoints to Expose

Instead of using `OpenAPISchemaSymbol::All` you can instead create your own
enum that implements `Distantmagic\Resonance\OpenAPISchemaSymbolInterface`
and use it to mark the responders.
