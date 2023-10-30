---
collections: 
    - documents
layout: dm:document
parent: docs/features/database/doctrine/index
title: Entity Managers
description: >
    Use Doctrine Entity Managers with Swoole's connection pools under the hood.
---

# Entity Managers

:::note
You should *not* follow the 
[Doctrine's tutorial on obtaining the EntityManager](https://www.doctrine-project.org/projects/doctrine-orm/en/current/tutorials/getting-started.html#obtaining-the-entitymanager).

Resonance provides a custom driver to integrate Doctrine with asynchronous PHP
environment and {{docs/features/database/swoole/connection-pools}}. 

Read the "Usage" section for details.
:::

# Usage

## Dependency Injection

In general, you can follow 
[Doctrine's documentation](https://www.doctrine-project.org/projects/orm) but
there are two primary differences:

1. Obtaining `DBAL` connections: you shouldn't use Doctrine's adapters to 
    obtain the `DBAL` connection - you should rely on 
    `Distantmagic\Resonance\DoctrineConnectionRepository`
    instead as it uses the {{docs/features/database/swoole/connection-pools}} 
    under the hood and manages them transparently.
2. Obtaining `EntityManager`:  use 
    `Distantmagic\Resonance\DoctrineEntityManagerRepository` instead of 
    building Doctrine's `Doctrine\ORM\EntityManager` manually.

This is because Resonance is using a custom driver to make Doctrine work in an
async environment with connection pools.

For example, manual usage in {{docs/features/http/responders}}:

```php file:app/DoctrineEntity/BlogPost.php
<?php

namespace App\DoctrineEntity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table('blog_posts')]
class BlogPost
{
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    public DateTime $createdAt;

    #[ORM\Column(name: 'fold')]
    public string $fold;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    public ?int $id = null;

    #[ORM\Column]
    public string $slug;

    #[ORM\Column]
    public string $title;
}
```

```php file:app/HttpResponder/Blog.php
<?php

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

## Controller Parameter Injection

You can skipp all the tedious steps related to using the Dependency Injection 
manually and opt for {{docs/features/http/controllers}} instead.

You can use Resonance's `DoctrineEntityRepository` attribute to inject a 
an `EntityRepository` for the given model:

```php file:app/HttpResponder/Blog.php
<?php

namespace App\HttpResponder;

use App\DoctrineEntity\BlogPost;
use App\HttpRouteSymbol;
use Distantmagic\Resonance\Attribute\DoctrineEntityRepository;
use Distantmagic\Resonance\Attribute\RespondsToHttp;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpResponder\HttpController;
use Distantmagic\Resonance\RequestMethod;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\TwigTemplate;
use Doctrine\ORM\EntityRepository;

#[RespondsToHttp(
    method: RequestMethod::GET,
    pattern: '/blog',
    routeSymbol: HttpRouteSymbol::Blog,
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
final readonly class Blog extends HttpController
{
    public function handle(
        #[DoctrineEntityRepository(BlogPost::class)]
        EntityRepository $blogPosts,
    ): HttpInterceptableInterface {
        return new TwigTemplate('website/blog.twig', [
            'blog_posts' => $blogPosts->findAll(),
        ]);
    }
}
```
