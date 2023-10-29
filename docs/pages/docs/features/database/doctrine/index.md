---
collections: 
    - name: documents
      next: docs/features/database/migrations/index
layout: dm:document
parent: docs/features/database/index
title: Doctrine
description: >
    Use Doctrine on top of Swoole's connection pools and Resonance to obtain
    it's ORM features.
---

# Doctrine

Resonance provides integration with Doctrine's 
[DBAL](https://www.doctrine-project.org/projects/dbal.html)
and [ORM](https://www.doctrine-project.org/projects/orm.html). 

Doctrine integration uses the same database configuration as 
{{docs/features/database/swoole/index}} database integration.

## Under the Hood

The integration is opinionated, so we picked the configuration defaults to best 
match the Swoole environment.

Those are:

1. Doctrine uses in-memory local cache. No file cache nor Redis cache is 
    necessary because all the metadata is loaded just once, and Doctrine keeps 
    it in a PHP array.
2. Doctrine uses only attribute mapping. That is in line with other 
    Resonance's features.
3. Doctrine integration uses {{docs/features/database/swoole/connection-pools}}
    and it does so transparently.

# Usage

In general, you can follow 
[Doctrine's documentation](https://www.doctrine-project.org/projects/orm). 
The only caveat is obtaining `DBAL` connections. You shouldn't use Doctrine's
adapters to obtain the `DBAL` connection - you should rely on 
`Distantmagic\Resonance\DoctrineConnectionRepository` instead as it uses 
the {{docs/features/database/swoole/connection-pools}} under the hood and 
manages them transparently.

If you want to use {{docs/features/database/swoole/connection-pools}} and
other Resonance's features, you should use the 
`Distantmagic\Resonance\DoctrineEntityManagerRepository` instead of 
building Doctrine's `Doctrine\ORM\EntityManager` manually.

Other than that, you can follow Doctrine documentation.

## Controllers

You should use `Distantmagic\Resonance\DoctrineEntityManagerRepository` to 
obtain the `Doctrine\ORM\EntityManager`. 

:::caution
You shouldn't use the `Doctrine\DBAL\DriverManager` to obtain the connection.
`Distantmagic\Resonance\DoctrineEntityManagerRepository` does that and takes 
care of integration with Resonance and Swoole.

It makes sure that after each request, the connection used by the `DBAL`
and `EntityManager`
is returned to the {{docs/features/database/swoole/connection-pools}}.
:::

```php
<?php

declare(strict_types=1);

namespace App\HttpResponder;

use App\DoctrineEntity\BlogPost;
use App\HttpRouteSymbol;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\DoctrineEntityManagerRepository;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\RequestMethod;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\TwigTemplate;
use Swoole\Http\Request;
use Swoole\Http\Response;

#[RespondsToHttp(
    method: RequestMethod::GET,
    pattern: '/blog',
    routeSymbol: HttpRouteSymbol::Blog,
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
final readonly class Blog extends HttpResponder
{
    public function __construct(
        private DoctrineEntityManagerRepository $doctrineEntityManagerRepository,
    ) {}

    public function respond(Request $request, Response $response): HttpInterceptableInterface
    {
        $entityManager = $this->doctrineEntityManagerRepository->getEntityManager($request);

        $blogPostsRepository = $entityManager->getRepository(BlogPost::class);

        return new TwigTemplate('turbo/website/blog.twig', [
            'blog_posts' => $blogPostsRepository->findAll(),
        ]);
    }
}
```
