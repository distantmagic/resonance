---
collections: 
    - name: documents
      next: docs/features/generating-static-sites/index
layout: dm:document
next: docs/features/generating-static-sites/deploying-to-github-pages
parent: docs/features/generating-static-sites/index
title: Markdown Extensions
description: >
    Learn about the details of Markdown processing inside Resonance's static
    site generator.
---

# Markdown Extensions

Under the hood, Resonance uses the 
[`League\Commonmark`](https://commonmark.thephpleague.com/)
library. By default, it has several plugins enabled. Resonance's Static Site 
Generator requires those extensions, and thus, you should not disable them:

- [Description Lists](https://commonmark.thephpleague.com/2.4/extensions/description-lists/)
- [External Links](https://commonmark.thephpleague.com/2.4/extensions/external-links/)
- [Github-Flavored Markdown](https://commonmark.thephpleague.com/2.4/extensions/github-flavored-markdown/)
- [Heading Permalinks](https://commonmark.thephpleague.com/2.4/extensions/heading-permalinks/)

Besides those plugins that are distributed with 
[`League\Commonmark`](https://commonmark.thephpleague.com/), Resonance adds a few
custom extensions.

## Admonitions

Adomnitions follow the [Docusaurus](https://docusaurus.io/docs/markdown-features/admonitions)
syntax (three colons, then type string).

For example, this markdown code:

```markdown
:::my-admonition-type
My admonition.
:::
```

Is going to be transformed into:

```
<div class="admonition admonition--my-admonition-type">
My admonition.
</div>
```

The admonition type is arbitrary; you can name it in any way you need. To 
customize them, you need to use CSS. A few types of admonitions are styled by 
default:

:::caution
Admonition of type `caution`.
:::
:::danger
Admonition of type `danger`.
:::
:::info
Admonition of type `info`.
:::
:::tip
Admonition of type `tip`.
:::

## Linking Pages

You can use the `{{page-reference}}` syntax to link between pages.

For example, let's assume there is a `test.md` page with the following 
contents:

```yaml
---
title: My Page
description: My Description
layout: dm:document
---

Hello!
```

Using `{{test}}` is going to produce the following link:

```
<a href="/test.html">My Page</a>
```

## Table of Contents

This is not an extension; instead, it's a library that can process the
[`League\Commonmark`](https://commonmark.thephpleague.com/) parsed document
to build the Table of Contents from the headings it finds.

It's used in the default `document` layout to generate the table of contents.

You can use it programmatically like so:

```php
<?php

use League\CommonMark\Output\RenderedContent;
use Distantmagic\Resonance\CommonMarkTableOfContentsBuilder;

$tableOfContentsBuilder = new CommonMarkTableOfContentsBuilder();

/**
 * @var RenderedContent $renderedOutput
 */
$renderedDocument = $renderedOutput->getDocument();

$tableOfContentsLinks = $tableOfContentsBuilder->getTableOfContentsLinks($renderedDocument);

foreach ($tableOfContentsLinks as $link) {
    $link->level; // nesting level
    $link->slug; // to be used in the url #hash
    $link->text; // text contents of a heading
}
```
