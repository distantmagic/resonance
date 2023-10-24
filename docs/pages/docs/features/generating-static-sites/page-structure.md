---
collections: 
    - name: documents
      next: docs/features/generating-static-sites/providing-layouts
layout: dm:document
next: docs/features/generating-static-sites/providing-layouts
parent: docs/features/generating-static-sites/index
title: Page Structure
description: >
    Learn how to structure a Markdown page so it can be used with the Static
    Site Generator.
---

# Page Structure

Static pages consist of two parts: *Front Matter* and *Content*.

## Front Matter

Front Matter is a YAML block placed at the top of a Markdown file. It serves
as a list of metadata properties.

We borrowed the concept of Front Matter from 
[Jekyll](https://jekyllrb.com/docs/front-matter/). However, 
contrary to Jekyll, Resonance validates all the Front Matter, and you can only 
use a predefined set of fields specific to Resonance.

For example:

```yaml
---
layout: dm:document
title: Example Document
description: This document is about...
---
```

### Supported Fields

Field                             | Type                 | Description
-|-|-|-
`layout`                          | `string`             | Learn more about {{docs/features/generating-static-sites/providing-layouts}} on it's documentation page.
`title`                           | `string`             | Used in `<title>` tag and when referencing this page from other pages.
`description`                     | `string`             | Used in `<meta name="description">` tag and when previewing this page from other pages.
`collections` (optional)          | `array<page-basename\|{ name: string, next: page-basename }>` | Assign this page to a collection. Collection names can be arbitrary. Resonance supports `"primary_navigation"` and `"documents"` collections.<br><br>`next` is used to organize the items order, it tells which other page should follow this one. It does **not** mean that the `next` page is **immediately** next after this one - just that it should be somewhere after this one in the given collection.
`content_type` (optional)         | `"html"` or `"markdown"` | If it's set to `"html"` then the page content is not parsed, it's forwarded to the layout as-is.
`draft` (optional)                | `bool`               | If set to true, this page will be ignored by static page generator as if it doesn't exist.
`next` (optional)                 | `page-basename`      | Tells the generator which page is **immediately** next to this one (if it's a part of a series for example). It should follow the same logic as with [`<link rel="next">`](https://developers.google.com/search/blog/2011/09/pagination-with-relnext-and-relprev).<br><br>In documentation pages it's used to generate previous / next links in a footer.
`parent` (optional)               | `page-basename`      | Tells the generator which page is higher in hierarchy of documents than this one.<br><br>In documentation it's used to generate menus with nested elements.
`register_stylesheets` (optional) | `array<string>`      | You can add extra stylesheets to this page. It follows the {{docs/features/asset-bundling-esbuild/index}} rules.<br><br>It's also used to generate `<link rel="preload">` tags, so you might want to keep stylesheets fine-grained to avoid unnecessary preloads.

### Page Basename

`page-basename` is a `string` that references another page. The static page 
generator validates it and then raises an error if the referenced page does not 
exist or if it's a draft.

Page basename is a relative path to that page without an extension.

For example, let's consider a project like this:

```
pages/
├─ features/
│  ├─ feature_foo.md
│  ├─ feature_bar.md
├─ homepage.md
```

If `pages/` is the base directory, then `feature_foo.md` has a `page-basename`
path equal to `features/feature_foo`, `homepage.md` has a basename of 
`homepage`, etc.

## Content

Depending on what `content_type` the page has (`"html"` or `"markdown"`), you 
can use either of those. 

`"html"` is not parsed by the static page generator
at all (just passed to the layout), so we are going to focus on Markdown 
instead.

:::note
Using `"html"` content type does not mean that the entire layout is replaced 
with the given code - just the contents.

The chosen layout decides where to put the static page contents.
:::

