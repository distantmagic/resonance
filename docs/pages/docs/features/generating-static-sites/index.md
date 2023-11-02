---
collections: 
    - documents
layout: dm:document
parent: docs/features/index
title: Generating Static Sites
description: >
    Learn how to use Resonance's built-in static site generator.
---

# Generating Static Sites

Resonance provides a PHP Static Site Generator. This documentation is created 
with Resonance.

Static Site Generator (SSG) is here because Distantmagic\Resonance focuses on 
interoperability between services: after developing your website and API, you 
should document it.

You can do so without installing an additional technology stack and using PHP 
with Swoole extension.

## Using the Default Command

By default, Resonance ships with the `static-pages:build` command 
(`php ./bin/resonance.php static-pages:build`) which uses `docs/` directory 
to build your documentation and the `build/` directory to store the output 
files.

You can modify that command to suit your project's needs.

## Programmatic Usage

All {{docs/features/configuration/index}} values are loaded from the 
`config.ini` file.

Parameter | Description
-|-
`base_url` | all documentation links are going to be relative to this URL
`esbuild_metafile` | learn more about esbuild metafile in {{docs/features/asset-bundling-esbuild/index}}. You can use all asset bundling features (including assets preloading) in static pages.
`input_directory` | location of your input Markdown files
`output_directory` | this is where your files are going to be saved
`sitemap` | this is where `sitemap.xml` will be saved

```php
<?php

namespace App;

require_once __DIR__.'/../vendor/autoload.php';

defined('DM_ROOT') or exit('Configuration is not loaded.');

use Distantmagic\Resonance\DependencyInjectionContainer;
use Distantmagic\Resonance\StaticPageProcessor;
use Swoole\Runtime;

use function Swoole\Coroutine\run;

Runtime::enableCoroutine(SWOOLE_HOOK_ALL);

$container = new DependencyInjectionContainer();
$container->phpProjectFiles->indexDirectory(DM_RESONANCE_ROOT);
$container->phpProjectFiles->indexDirectory(DM_APP_ROOT);
$container->registerSingletons();

$staticPageProcessor = $container->make(StaticPageProcessor::class);

/**
 * @var bool
 */
$coroutineResult = run(static function () use ($staticPageProcessor) {
    $staticPageProcessor->process();
});

exit((int) !$coroutineResult);
```

{{docs/features/generating-static-sites/*!docs/features/generating-static-sites/index}}
