---
collections: 
    - documents
layout: dm:document
next: docs/features/asset-bundling-esbuild/cache-busting
parent: docs/features/asset-bundling-esbuild/index
title: Accessing Entrypoints in PHP
description: >
    Access esbuild entrypoints in PHP by parsing esbuild metafile.
---

# Accessing Entrypoints in PHP

## Example project

Consider this project with TypeScript and CSS files that we'll use as an 
example throughout this documentation page:

```
resources/
├─ css/
│  ├─ app.css
├─ ts/
│  ├─ controller_homepage.ts
│  ├─ controller_search.ts
│  ├─ common.ts
├─ fonts/
│  ├─ myfont.ttf
```

```css file:app.css
@font-face {
  font-family: "myfont";
  src: url("../../resources/myfont.ttf") format("truetype");
}

body {
    background-color: red;
}
```

```typescript file:controller_homepage.ts
import common from "./common.ts";

// some application code
// (...) 
````

```typescript file:controller_search.ts
import common from "./common.ts";

// some application code
// (...) 
````

## Building the Front-end

To access esbuild entrypoints data in PHP, you must first generate 
the [build metafile](https://esbuild.github.io/api/#metafile).

Let's assume you're building your project using the following command:

```shell
$ ./node_modules/.bin/esbuild \
    --bundle \
    --format=esm \
    --loader:.ttf=file \
    --metafile=esbuild-meta.json \
    --outdir=assets \
    --splitting \
    resources/css/app.css \
    resources/ts/controller_homepage.ts \
    resources/ts/controller_search.ts \
;
```

With this command, your application generates three endpoints:

- `app.css`
- `controller_homepage.ts`
- `controller_search.ts`

In addition to these endpoints, esbuild also creates a metafile:

- `esbuild-meta.json`

## Processing esbuild Metadata in PHP

To refer to entrypoints by their input names (rather than output names), you 
must parse the esbuild metafile. This ensures you obtain the correct 
filenames.

If you host your front-end assets on a CDN server, you can simply include the 
esbuild metafile alongside your PHP code and then load the target files from 
the CDN.

All the information about entrypoints and their dependencies is available 
through the `Resonance\EsbuildMeta` object:

```php
<?php

use Distantmagic\Resonance\EsbuildMetaBuilder;
use Distantmagic\Resonance\EsbuildMetaEntryPoints;
use Distantmagic\Resonance\EsbuildMeta;

$esbuildMetaBuilder = new EsbuildMetaBuilder();

/**
 * @var EsbuildMeta $esbuildMeta
 */
$esbuildMeta = $esbuildMetaBuilder->build(__DIR__.'/esbuild-meta.json');
$entryPoints = new EsbuildMetaEntryPoints($esbuildMeta);

$entryPointPath = $entryPoints->resolveEntryPointPath('controller_homepage.ts');
```

By default, `$entryPointPath` will point to `assets/ts/controller_homepage.js`, 
though the filename may vary depending on your configuration. This is why it's 
crucial to be able to refer to your assets using their original filenames.

You can now use the obtained path in your template:

```php
echo <<<HTML
<script type="module" src="{$entryPointPath}"></script>
HTML;
```
