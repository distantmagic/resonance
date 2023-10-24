---
collections: 
    - documents
layout: dm:document
parent: docs/features/index
title: Asset Bundling (esbuild)
description: >
    Use Resonance's built-in features to support assets processed by esbuild.
---

# Asset Bundling (esbuild)

Currently, esbuild is the only supported asset bundler in Resonance.

## What is esbuild?

[esbuild](https://esbuild.github.io/) is an exceptionally fast bundler designed 
for web development. It can process a wide range of web assets (CSS, 
JavaScript, TypeScript, and more).

## Integrating esbuild with Resonance

Resonance provides tools that streamline the integration of esbuild 
into your PHP projects. Most of them rely on the esbuild's metafile, so you can
use esbuild the way you want - you need to output the meta build file:

```shell
$ esbuild app.js --bundle --metafile=meta.json --outfile=out.js
```

{{docs/features/asset-bundling-esbuild/*!docs/features/asset-bundling-esbuild/index}}
