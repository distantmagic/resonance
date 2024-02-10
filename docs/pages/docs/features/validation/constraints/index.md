---
collections: 
    - name: documents
      next: docs/features/validation/form-models/index
layout: dm:document
next: docs/features/validation/form-models/index
parent: docs/features/validation/index
title: Constraints Schema
description: >
    Learn how to build validation schemas to check your incoming data.
---

# Constraints Schema

Resonance provides validation constraints that are roughly equivalent to what
[JSON Schema](https://json-schema.org/) offers.

They are written in PHP, but they are also convertible into JSON Schema 
(they feature is also used internally by Resonance's 
{{docs/features/openapi/index}} schema generator). If you need to, you can
export your schemas to JSON.

# Usage

All constraints are in `Distantmagic\Resonance\Constraint` namespace.

## Schema

### Any 

```php
new AnyConstraint();
```
```json
{}
```

Accepts any value.

### Any of

```php
/**
 * @var array<Constraint> $anyOf 
 */
new AnyOfConstraint(anyOf: $anyOf);
```
```json
{ 
    "anyOf": [...] 
}
```

Acceepts a value if it passess any of the listed constraints.

### Boolean

```php
new BooleanConstraint();
```
```json
{ 
    "type": "boolean"
}
```

### Const

```php
/**
 * @var int|float|string $constValue
 */
new ConstConstraint(constValue: $constValue);
```
```json
{ 
    "const": ...
}
```

Accepts exactly the provided value

### Enum

```php
/**
 * @var array<string>|list<string> $values
 */
new EnumConstraint(values: $values);
```
```json
{ 
    "type": "string",
    "enum": [...]
}
```

### Integer

```php
new IntegerConstraint();
```
```json
{ 
    "type": "integer"
}
```

### List

```php
/**
 * @var Constraint $constraint
 */
new ListConstraint(valueConstraint: $constraint);
```
```json
{ 
    "type": "array", 
    "items": ... 
}
```

Accepts an array only if each array's item validates agains the constraint.

### Map

```php
/**
 * @var Constraint $constraint
 */
new MapConstraint(valueConstraint: $constraint);
```
```json
{ 
    "type": "object", 
    "additionalProperties": ... 
}
```

Accepts an object if each property validates agains the constraint.

### Number

```php
new NumberConstraint();
```
```json
{ 
    "type": "number"
}
```

Accepts both integer and float values.

### Object

```php
/**
 * @var array<non-empty-string,Constraint> $properties
 */
new ObjectConstraint(properties: $properties);
```
```json
{ 
    "type": "object", 
    "properties": ... 
}
```

Expects an object with exactly the listed properties.

### String

```php
new StringConstraint();
```
```json
{ 
    "type": "string",
    "minLength": 1
}
```

### Tuple

```php
/**
 * @var list<Constraint> $items
 */
new TupleConstraint(items: $items);
```
```json
{ 
    "type": "array",
    "items": false,
    "prefixItems": ...
}
```

Expects an array of exactly the provided shape and length.

## Exporting to JSON Schema

You can use `toJsonSchema()` method. Every constraint has it:

```php
$constraint = new TupleConstraint([
    new StringConstraint(),
    new NumberConstraint(),
]);

$constraint->toJsonSchema();
```

Produces:

```php
[
    'type' => 'array',
    'items' => false,
    'prefixItems' => [
        [
            'type' => 'string',
            'minLength' => 1,
        ],
        [
            'type' => 'number',
        ],
    ],
]
```

## Validation Errors

After validation you can inspect the returned `ConstraintResult` object to 
check for error messages and the mapped data. Validators always cast data
to associative arrays.

```php
$constraint = new ListConstraint(
    valueConstraint: new StringConstraint()
);

$validatedResult = $constraint->validate(['hi', 5]);

if (!$validatedResult->status->isValid()) {
    /**
     * Errors are indexed by the field name, value is the error code.
     * 
     * @var Map<string,non-empty-string> $errors
     */
    $errors = $validatedResult->getErrors();
}
```

Possible error codes are:

- `invalid_data_type`
- `invalid_enum_value`
- `invalid_format`
- `invalid_nested_constraint`
- `missing_property`
- `ok`
- `unexpected_property`

## Examples

You can compose constraints together:

```php
$constraint = new ObjectConstraint(
    properties: [
        'host' => new StringConstraint(),
        'port' => new IntegerConstraint(),
    ],
);

$constraint->validate([
    'host' => 'http://example.com',
    'port' => 3306,
]);
```
