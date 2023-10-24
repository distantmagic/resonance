---
collections: 
    - documents
layout: dm:document
parent: docs/features/generating-static-sites/index
title: Deploying to GitHub Pages
description: >
    Learn how to deploy Resonance's static page using GitHub Actions example 
    workflow.
---

# Deploying to GitHub Pages

## Prerequisites

You need to configure a runner with `php` and all Resonance's required 
extensions. Learn more about them at:
{{docs/getting-started/installation-and-requirements}}.

## Building the Artifact

Replace `"./build_directory"` with your Static Site Generator output directory:

```shell
$ tar
    --dereference --hard-dereference
    -cvf "docs/artifact.tar"
    --exclude=.git
    --exclude=.github
    --directory "./build_directory" .
```

## GitHub Actions Workflow

```yaml
name: github pages

on:
  workflow_dispatch:
  push:
    branches:
      - master

# Sets permissions of the GITHUB_TOKEN to allow deployment to GitHub Pages
permissions:
  contents: read
  pages: write
  id-token: write

concurrency:
  group: "pages"
  cancel-in-progress: false

jobs:
  build:
    runs-on:
      - linux
      - self-hosted
    steps:
      - name: checkout
        uses: actions/checkout@v3

      - name: setup pages
        uses: actions/configure-pages@v3

      - name: create static pages artifact
        run: BUILD_ID=$GITHUB_SHA make docs/artifact.tar
        env:
          APP_ENV: ${{ vars.APP_ENV }}

      - name: Upload artifact
        uses: actions/upload-artifact@v3
        with:
          name: github-pages
          path: docs/artifact.tar
          retention-days: 1
          if-no-files-found: error

  deploy:
    environment:
      name: github-pages
      url: ${{ steps.deployment.outputs.page_url }}
    runs-on:
      - linux
      - self-hosted
    needs: build
    steps:
      - name: Deploy to GitHub Pages
        id: deployment
        uses: actions/deploy-pages@v2
```
