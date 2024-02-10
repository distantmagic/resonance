---
collections: 
    - name: documents
      next: docs/features/validation/http-controller-parameters/index
layout: dm:document
next: docs/features/validation/http-controller-parameters/index
parent: docs/features/validation/index
title: Form Models
description: >
    Validate HTML forms, incoming WebSocket messages and more.
---

# Form Models

Resonance can map incoming data into models representing a validated data.

It is a useful abstraction that allows you to make sure you are using validated
data in your application.

# Usage

We will use `BlogPostCreateForm` as an example. It represents a request to
create a blog post:

```php
<?php

namespace App\InputValidatedData;

use Distantmagic\Resonance\InputValidatedData;

readonly class BlogPostCreateForm extends InputValidatedData
{
    public function __construct(
        public string $content,
        public string $title,
    ) {}
}

```

## Validators

Validators take in any data and check if it adheres to the configuration 
schema. The `getConstraint()` method must return a 
{{docs/features/validation/constraints/index}} object.

```php
<?php

namespace App\InputValidator;

use App\InputValidatedData\BlogPostCreateForm;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\Constraint\ObjectConstraint;
use Distantmagic\Resonance\Constraint\StringConstraint;
use Distantmagic\Resonance\InputValidator;
use Distantmagic\Resonance\SingletonCollection;

/**
 * @extends InputValidator<BlogPostCreateForm, array{
 *     content: string,
 *     title: string,
 * }>
 */
#[Singleton(collection: SingletonCollection::InputValidator)]
readonly class BlogPostFormValidator extends InputValidator
{
    public function castValidatedData(mixed $data): BlogPostCreateForm
    {
        return new BlogPostCreateForm(
            $data['content'],
            $data['title'],
        );
    }

    public function getConstraint(): Constraint
    {
        return new ObjectConstraint(
            properties: [
                'content' => new StringConstraint(),
                'title' => new StringConstraint()
            ],
        );
    }
}
```

Preferably validators should be injected somewhere by the 
{{docs/features/dependency-injection/index}}, so you don't have to set up their 
parameters manually. Then you can call their `validateData()` method.

```php
<?php

use Distantmagic\Resonance\InputValidatorController;
use Distantmagic\Resonance\InputValidationResult;

$inputValidatorController = new InputValidatorController();

/**
 * @var InputValidationResult $validationResult
 */
$validationResult = $inputValidatorController->validateData($blogPostFormValidator, [
    'content' => 'test',
    'title' => 'test',
]);

// If validation is successful, the errors list is empty and validation data
// is set.
assert($validationResult->constraintResult->getErrors()->isEmpty());

/**
 * It's null if validation failed.
 * 
 * @var ?BlogPostCreateForm $validationResult->inputValidatedData
 */
assert($validationResult->inputValidatedData);
```

Validators do not throw an exception since invalid data is not a failure in the
intended application flow. Instead, it's just a normal situation to handle.
