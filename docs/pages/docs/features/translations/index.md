---
collections: 
    - documents
layout: dm:document
parent: docs/features/index
title: Translations
description: >
    Serve your application in the users preferred languages.
---

# Translations

Translations are loaded from `.ini` files that are located in the translations
base directory:

```ini file:config.ini
; ...

[translator]
base_directory = app/lang
default_primary_language = en

; ...
```

# Usage

Let's assume we have the following translations file and our default language
is `en`:

```ini file:app/lang/my.ini
hello = "world"
greeting = "Hello, :name!"

[planet]
ours = "Earth"
```

## PHP

In the below case, the `"my.hello"` phrase is going to be loaded from the
`app/lang/en/my.ini` file.

The `Request` object is used to determine the current client's language (by 
default by using the [Accept-Language](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Language)
header).

```php
<?php

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\TranslatorBridge;
use Swoole\Http\Request;

#[Singleton]
readonly class MyClass
{
    public function __construct(private TranslatorBridge $translator)
    {
    }

    public function doSomething(Request $request): string
    {
        return $this->translator->trans(
            $request, 
            'my.hello',
        );
    }
}
```

### Passing parameters

To pass parameters to your translation strings you need to 
provide the `TranslatorBridge::trans` method an associative
array of values.

```php
/**
 * @var \Distantmagic\Resonance\TranslatorBridge $translator
 */
$translator->trans($request, 'my.greeting', [
    'name' => 'Resonance',
])
```

## Twig

You can use the `trans` filter. The code below outputs `Earth`:

```twig
{{ 'my.planet.ours'|trans(request) }}
```

### Passing parameters

To provide parameters to the translation string, you have to pass an
object with values to the `trans` filter.

```twig
{{ 'my.greeting'|trans(request, {name: 'Magic'}) }}
```

You can learn more info about twig in its documentation page: 
{{docs/features/templating/twig/index}}.
