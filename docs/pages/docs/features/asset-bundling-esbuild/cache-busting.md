---
collections: 
    - documents
layout: dm:document
next: docs/features/asset-bundling-esbuild/preloading-assets
parent: docs/features/asset-bundling-esbuild/index
title: Cache Busting
description: >
    Prevent unwanted caching with esbuild features.
---

# Cache Busting

When you serve front-end assets through a CDN, these assets are often cached. 
To guarantee that your users receive the latest version of assets, clearing 
your CDN's cache after each deployment may not be sufficient, as client-side 
caching can still occur. An alternative approach is to generate unique 
filenames for your assets. This documentation focuses on this method.

Before diving into Cache Busting, it's important to understand 
{{docs/features/asset-bundling-esbuild/accessing-entrypoints-in-php}}.

## Building the Project

esbuild simplifies the process by allowing you to include a content hash in the 
output filenames:

```shell
$ ./node_modules/.bin/esbuild \
    --asset-names="./[name]_[hash]" \
    --entry-names="./[name]_[hash]" \
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

:::tip
For a more deterministic yet unique approach, you can provide your own build 
identifier, such as one derived from your latest GIT commit hash:

```shell
--asset-names="./[name]_$(BUILD_ID)"
```
:::

## Resolving Filenames in PHP

Since we retrieve filenames from the esbuild metafile, the process 
remains the same as described in
{{docs/features/asset-bundling-esbuild/accessing-entrypoints-in-php}}.

By including the content hash or a custom build identifier in the filenames, 
you ensure that each deployment results in a unique filename, effectively 
bypassing caching issues and guaranteeing that your users receive the latest 
assets.
