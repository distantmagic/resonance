---
collections: 
    - documents
layout: dm:document
parent: docs/features/validation/index
title: Http Controller Parameters
description: >
    Validate and map any incoming data into reusable objects.
---

# HTTP Controller Paramateres

:::note
This feature only works in HTTP {{docs/features/http/controllers}}. It doesn't
work with pure {{docs/features/http/responders}}.
:::

Controllers allow to validate and map incoming HTTP data into reusable objects.

# Usage

## Attributes

You don't have to call the validator manually if you use 
{{docs/features/http/controllers}}. You can use parameter attributes instead.

For example, if a `MyValidator` exists that returns `MyValidatedData` model, 
you can add `#[ValidatedRequest(MyValidator::class)]` annotation to the 
parameter of the `MyValidatedData`. A controller is going to validate the 
incoming `POST` data using the `MyValidator`, and in case of success, it's 
going to inject the data model into the parameter:

```php
// ...

public function createResponse(
    #[ValidatedRequest(MyValidator::class)]
    MyValidatedData $data,
) {
    // ...
}

// ...
```

## Error Handling

To handle errors, you can create an optional method marked in the
`#[OnParameterResolution]` attribute. If such a method exists, then will be 
be called in case of validation failure. 

If no such method exists, the controller is going to return a generic
`400 Bad Request` response.

:::caution
`handle` method's arguments are forwarded into the error validation method.

It can only use the parameters that are already resolved in the `handle` method
plus an extra argument with validation errors and request/response pair.

Adding new arguments to the error handler besides those is going to cause an
error.
:::

```php
<?php

namespace App\HttpResponder;

use App\HttpRouteSymbol;
use App\InputValidatedData\BlogPostForm;
use App\InputValidator\BlogPostFormValidator;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\OnParameterResolution;
use Distantmagic\Resonance\Attribute\ValidatedRequest;
use Distantmagic\Resonance\HttpControllerParameterResolution;
use Distantmagic\Resonance\HttpControllerParameterResolutionStatus;
use Distantmagic\Resonance\HttpResponder\HttpController;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\RequestMethod;
use Distantmagic\Resonance\SingletonCollection;
use Ds\Map;
use Ds\Set;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[RespondsToHttp(
    method: RequestMethod::POST,
    pattern: '/blog_post/create',
    routeSymbol: HttpRouteSymbol::BlogPostCreate,
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
final readonly class BlogPostStore extends HttpController
{
    public function createResponse(
        #[ValidatedRequest(BlogPostFormValidator::class)]
        #[OnParameterResolution(
            status: HttpControllerParameterResolutionStatus::ValidationErrors,
            forwardTo: 'handleValidationErrors',
        )]
        BlogPostForm $blogPostForm,
    ): HttpResponderInterface {
        /* inser blog post, redirect, etc */
        /* ... */
    }

    /**
     * @param Map<string,Set<string>> $errors
     */
    public function handleValidationErrors(
        ServerRequestInterface $request,
        ResponseInterface $response,
        HttpControllerParameterResolution $resolution,
    ): HttpResponderInterface {
        $response = $response->withStatus(400);

        /* render form with errors  */
        /* ... */
    }
}
```
