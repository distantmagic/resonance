---
collections: 
    - documents
layout: dm:document
next: docs/features/database/doctrine/entity-managers
parent: docs/features/database/doctrine/index
title: Entities
description: >
    Integrate Doctrine Entities with Resonance's features.
---

# Entities

Entities are a part of 
[Doctrine's ORM](https://www.doctrine-project.org/projects/orm.html). In 
general terms, they are PHP objects that represent database records.

If you want to learn how to use them, please refer to Doctrine documentation. 
They work the same as in synchronous PHP environments, with a few 
caveats related to obtaining DBAL connections. You can learn more about those 
in the {{docs/features/database/doctrine/index}} feature page.

Here, we will describe how you can integrate them with Resonance's 
features.

# Usage

## Obtaining from Entity Managers

You can learn more at {{docs/features/database/doctrine/entity-managers}} page.

## Controller Parameter Injection

You can use the `Distantmagic\Resonance\Attribute\DoctrineEntityRouteParameter`
to use an entity as {{docs/features/http/controllers}} parameter.

Both `intent` and `lookupField` are optional. By default `CrudAction::Read`
and `"id"` are used, respectively:

```php file:app/HttpResponder/BlogPostShow.php
<?php

namespace App\HttpResponder;

use App\DoctrineEntity\BlogPost;
use App\HttpRouteSymbol;
use Distantmagic\Resonance\Attribute\DoctrineEntityRouteParameter;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\CrudAction;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpResponder\HttpController;
use Distantmagic\Resonance\RequestMethod;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\TwigTemplate;

#[RespondsToHttp(
    method: RequestMethod::GET,
    pattern: '/blog/{blog_post_slug}',
    routeSymbol: HttpRouteSymbol::BlogPostShow,
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
final readonly class BlogPostShow extends HttpController
{
    public function handle(
        #[DoctrineEntityRouteParameter(
            from: 'blog_post_slug', 
            intent: CrudAction::Read,
            lookupField: 'slug',
        )]
        BlogPost $blogPost,
    ): HttpInterceptableInterface {
        return new TwigTemplate('turbo/website/blog_post.twig', [
            'blog_post' => $blogPost,
        ]);
    }
}
```

If you use an entity in a controller parameters, you might also need to add 
{{docs/features/security/authorization}} gate to determine who can use that
entity.
