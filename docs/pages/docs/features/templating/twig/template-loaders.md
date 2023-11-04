---
collections: 
    - documents
layout: dm:document
parent: docs/features/templating/twig/index
title: Template Loaders
description: >
    Learn how to register a template provider to load templates from various
    sources.
---

# Template Loaders

# Usage

## Default Loader

Resonance is bundled with a default template loader that looks for the `views`
directory in your `DM_APP_ROOT` (see: {{docs/features/configuration/index}}).

## Custom Loaders

If you want to add additional template loaders, you need to register them as
singletons and add to the `TwigLoader` collection.

For example:

```php file:app\TwigNamespacedFilesystemLoader.php
<?php

namespace App;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\TwigLoader;
use Distantmagic\Resonance\SingletonCollection;
use Twig\Loader\FilesystemLoader;

#[Singleton(collection: SingletonCollection::TwigLoader)]
#[TwigLoader]
class TwigNamespacedFilesystemLoader extends FilesystemLoader
{
    public function __construct()
    {
        parent::__construct(['/foo/bar/views']);
    }
}
```

## Optional Registration

You can use `Distantmagic\Resonance\TwigOptionalLoaderInterface` to make 
additional runtime checks before registering your twig loader. 

For example, you might want to check if some directory exists or not:

```php file:app/OptionalLoader.php
<?php

namespace App;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\TwigLoader;
use Twig\Loader\FilesystemLoader;

#[Singleton(collection: SingletonCollection::TwigLoader)]
#[TwigLoader]
class OptionalLoader extends FilesystemLoader implements TwigOptionalLoaderInterface
{
    public function __construct()
    {
        parent::__construct();
    }

    public function beforeRegister(): void
    {
        $this->addPath('/foo');
    }

    public function shouldRegister(): bool
    {
        return is_dir('/foo');
    }
}
```
