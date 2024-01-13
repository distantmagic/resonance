---
collections: 
    - documents
layout: dm:document
parent: docs/features/index
title: Validation
description: >
    Validate and convert any data. Learn how to validate HTML forms, incoming
    WebSocket messages and more.
---

# Validation

You can use validators to check if any incoming data is correct. For example:
form data filled by the user, incoming {{docs/features/websockets/index}} 
message, and others.

# Usage

## Validated Data Models (Form Models)

You should always map the validated data into the validated data models (also 
known as Form Models in other frameworks). 

They don't need any attributes or any configuration. Validated data is going to
be mapped onto those models later. 

An example blog post form model may look like this:

```php
<?php

namespace App\InputValidatedData;

use Distantmagic\Resonance\InputValidatedData;

readonly class BlogPostForm extends InputValidatedData
{
    public function __construct(
        public string $content,
        public string $title,
    ) {}
}

```

## Validators

Validators take in any data and check if it adheres to the configuration 
schema. The `makeSchema()` method must return a 
[JSON Schema](https://json-schema.org/) object.

```php
<?php

namespace App\InputValidator;

use App\InputValidatedData\BlogPostForm;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\InputValidator;
use Distantmagic\Resonance\JsonSchema;
use Distantmagic\Resonance\SingletonCollection;

/**
 * @extends InputValidator<BlogPostForm, array{
 *     content: string,
 *     title: string,
 * }>
 */
#[Singleton(collection: SingletonCollection::InputValidator)]
readonly class BlogPostFormValidator extends InputValidator
{
    protected function castValidatedData(mixed $data): BlogPostForm
    {
        return new BlogPostForm(
            $data['content'],
            $data['title'],
        );
    }

    protected function makeSchema(): Schema
    {
        return new JsonSchema([
            'type' => 'object',
            'properties' => [
                'content' => [
                    'type' => 'string',
                    'minLength' => 1
                ],
                'title' => [
                    'type' => 'string',
                    'minLength' => 1
                ]
            ],
            'required' => ['content', 'title']
        ]);
    }
}
```

Preferably validators should be injected somewhere by the 
{{docs/features/dependency-injection/index}}, so you don't have to set up their 
parameters manually. Then you can call their `validateData()` or
`validateRequest()` methods.

```php
<?php

use Distantmagic\Resonance\InputValidationResult;

/**
 * @var InputValidationResult $validationResult
 */
$validationResult = $blogPostFormValidator->validateData([
    'content' => 'test',
    'title' => 'test',
]);

// If validation is successful, the errors list is empty and validation data
// is set.
assert($validationResult->errors->isEmpty());

/**
 * It's null if validation failed.
 * 
 * @var ?BlogPostForm $validationResult->inputValidatedData
 */
assert($validationResult->inputValidatedData);
```

Validators do not throw an exception since invalid data is not a failure in the
intended application flow. Instead, it's just a normal situation to handle.

## Controller Attributes

:::note
This feature only works in HTTP {{docs/features/http/controllers}}. It doesn't
work with pure {{docs/features/http/responders}}.
:::

You don't have to call the validator manually if you use 
{{docs/features/http/controllers}}. You can use controller parameters instead.

For example, if a `MyValidator` exists that returns `MyValidatedData` model, 
you can add `#[ValidatedRequest(MyValidator::class)]` annotation to the 
parameter of the `MyValidatedData`. A controller is going to validate the 
incoming `POST` data using the `MyValidator`, and in case of success, it's 
going to inject the data model into the parameter:

```php
// ...

public function handle(
    #[ValidatedRequest(MyValidator::class)]
    MyValidatedData $data,
) {
    // ...
}

// ...
```

To handle errors, you can create an optional method marked with the
`#[ValidationErrorsHandler]` attribute. If such a method exists, then will be 
be called in case of validation failure. 

If no such method exists, the controller is going to return a generic
`400 Bad Request` response.

:::caution
`handle` method's arguments are forwarded into the error validation method.

It can only use the parameters that are already resolved in the `handle` method
plus an extra argument with validation errors (marked by the 
`#[ValidationErrors]`) and request/response pair.

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
use Distantmagic\Resonance\Attribute\ValidatedRequest;
use Distantmagic\Resonance\Attribute\ValidationErrors;
use Distantmagic\Resonance\Attribute\ValidationErrorsHandler;
use Distantmagic\Resonance\HttpResponder\HttpController;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\RequestMethod;
use Distantmagic\Resonance\SingletonCollection;
use Ds\Map;
use Swoole\Http\Request;
use Swoole\Http\Response;

#[RespondsToHttp(
    method: RequestMethod::POST,
    pattern: '/blog_post/create',
    routeSymbol: HttpRouteSymbol::BlogPostCreate,
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
final readonly class BlogPostStore extends HttpController
{
    public function handle(
        #[ValidatedRequest(BlogPostFormValidator::class)]
        BlogPostForm $blogPostForm,
    ): HttpResponderInterface {
        /* inser blog post, redirect, etc */
        /* ... */
    }

    /**
     * @param Map<string,string> $errors
     */
    #[ValidationErrorsHandler]
    public function handleValidationErrors(
        Request $request,
        Response $response,
        #[ValidationErrors]
        Map $errors,
    ): HttpResponderInterface {
        $response->status(400);

        /* render form with errors  */
        /* ... */
    }
}
```
