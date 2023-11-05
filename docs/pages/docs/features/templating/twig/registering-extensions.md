---
collections: 
    - name: documents
      next: docs/features/templating/twig/registering-template-loaders
layout: dm:document
next: docs/features/templating/twig/registering-template-loaders
parent: docs/features/templating/twig/index
title: Registering Extensions
description: >
    Learn how to register a Twig extension.
---

# Registering Extensions

# Usage

To register an extension, you need to add `#[TwigExtension]` attribute to the
extension class, register it as a singleton and add it to the `TwigExtension`
collection:

```php file:app\MyTwigExtensions.php
<?php

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\TwigExtension;
use Twig\Extension\ExtensionInterface;
use Twig\TwigFunction;

#[Singleton(collection: SingletonCollection::TwigExtension)]
#[TwigExtension]
readonly class MyTwigExtension implements ExtensionInterface
{
    // ...
}
```
