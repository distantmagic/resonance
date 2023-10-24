---
collections: 
    - name: documents
      next: docs/features/generating-static-sites/markdown-extensions
layout: dm:document
next: docs/features/generating-static-sites/markdown-extensions
parent: docs/features/generating-static-sites/index
title: Providing Layouts
description: >
    Learn how to provide custom layouts to the documentation pages.
---

# Providing Layouts

The role of the layout is to render the entire static page (with the `<html>`
tag and such). It needs to do so by using the 
`Distantmagic\Resonance\StaticPage` object, which represents one static page
with it's metadata.

You can register layouts using the 
`Distantmagic\Resonance\Attribute\StaticPageLayout` attribute.

Layout needs to be a singleton, added to the 
`SingletonCollection::StaticPageLayout` collection.

You need to implement the `renderStaticPage` method, which must return a 
string generator. Each string that is yielded is going to be asynchronously 
written to the output file immediately.

```php
<?php

namespace App\StaticPageLayout;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\StaticPageLayout;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\StaticPage;
use Distantmagic\Resonance\StaticPageContentRenderer;
use Generator;

#[Singleton(collection: SingletonCollection::StaticPageLayout)]
#[StaticPageLayout('my:page')]
readonly class MyStaticPageLayout implements StaticpageLayoutInterface
{
    public function __construct(
        private StaticPageContentRenderer $staticPageContentRenderer,
    ) {
    }

    /**
     * @return Generator<string>
     */
    public function renderStaticPage(StaticPage $staticPage): Generator
    {
        yield $this->staticPageContentRenderer->renderContent($staticPage);
    }
}
```
