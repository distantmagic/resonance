---
collections: 
    - name: documents
      next: docs/features/templating/twig/registering-extensions
layout: dm:document
next: docs/features/templating/twig/registering-extensions
parent: docs/features/templating/twig/index
title: Rendering Templates
description: >
    Learn how to render Twig templates and use built-in filters.
---

# Rendering Templates

## Usage

```php
<?php

namespace App\HttpResponder;

use App\HttpRouteSymbol;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\RequestMethod;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\TwigTemplate;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[RespondsToHttp(
    method: RequestMethod::GET,
    pattern: '/twig',
    routeSymbol: HttpRouteSymbol::Twig,
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
final readonly class Twig extends HttpResponder
{
    public function respond(ServerRequestInterface $request, ResponseInterface $response): HttpInterceptableInterface
    {
        return new TwigTemplate($request, $response, 'test.twig');
    }
}
```

## Filters

### `intl_format_date`

Formats date using PHP's `intl` extension. It needs the request object to 
determine the currently used language.

```twig
{{ '2023-10-07'|intl_format_date(request) }}
```

### `trans`

It needs the request object to determine the currently used language.

```twig
{{ 'my.message'|trans(request) }}
```

## Functions

### `can`

Determines if the current user can perform the given site action. Learn more 
about site actions at the {{docs/features/security/authorization/index}} page.

```twig
{% if can(request, constant('App\\SiteAction::CreateBlogPost')) %}
    {# ... #}
{% endif %}
```

### `can_crud`

Determines if the current user can perform the given CRUD action. Learn more 
about CRUD actions at the {{docs/features/security/authorization/index}} page.

```twig
{% if can_crud(request, blog_post, constant('Resonance\\CrudAction::Delete')) %}
    {# ... #}
{% endif %}
```

### `csp_include`

Adds an item to {{docs/features/security/content-security-policy/index}} rules.

```twig
<script src="{{ csp_include(request, 'script-src', 'https://example.com') }}"></script>
```

You can also suppress the output:

```twig
{{ csp_include(request, 'frame-src', 'https://frames.example.com', false) }}
```

### `csp_nonce`

Generates CSP nonce to be used with Content Security Policy headers. Some 
frontend framework require the CSP nonce to be set in the meta tags:

```twig
<meta name="csp-nonce" content="{{ csp_nonce(request) }}">
```

### `csrf_token`

Obtains CSRF token for the current request:

```twig
<input
    type="hidden"
    name="csrf"
    value="{{ csrf_token(request, response) }}"
>
```

### `esbuild`

Resolves the path of the compiled asset. Learn more at 
{{docs/features/asset-bundling-esbuild/index}}:

```twig
<script defer type="module" src="{{ esbuild(request, 'my_script.ts') }}"></script>
```

### `esbuild_preload`

Preloads the asset even if it's not explicitly required in a `<script>` or 
`<link>` tags. Learn more about preloading at 
{{docs/features/asset-bundling-esbuild/preloading-assets}}:

```twig
{{ esbuild_preload(request, 'my_script.ts') }}
{{ esbuild_preload(request, 'my_stylesheet.css') }}
```

### `esbuild_render_preloads`

Renders the preload meta-tags. Should be used after all `esbuild` and 
`esbuild_preload` calls in the template:

```twig
{% set assets %}
    <script defer type="module" src="{{ esbuild(request, 'global_service_worker.ts') }}"></script>
    <script defer type="module" src="{{ esbuild(request, 'global_turbo.ts') }}"></script>
    <script defer type="module" src="{{ esbuild(request, 'global_stimulus.ts') }}"></script>
{% endset %}

{{ esbuild_render_preloads(request) }}
{{ assets|raw }}
```

### `old`

Obtains the `POST` variable value from the current request:

```twig
<input
    name="title"
    type="text"
    value="{{ old(request, 'title', blog_post.title) }}"
>
```

### `route`

Generates URL that points to the requested route:

```twig
<a href="{{ route(constant('App\\HttpRouteSymbol::Homepage')) }}">
    {{ 'pages.homepage'|trans(request) }}
</a>
```

If you use just a string, it will try to use your `App\HttpRouteSymbol` file 
to match the route:

```twig
<a href="{{ route('Homepage') }}">
    {{ 'pages.homepage'|trans(request) }}
</a>
```
