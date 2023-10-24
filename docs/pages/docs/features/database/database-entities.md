---
collections:
    - name: documents
      next: docs/features/database/migrations
layout: dm:document
next: docs/features/database/migrations
parent: docs/features/database/index
title: Database Entities
description: >
    Learn how to map SQL queries results into reusable database entities.
---

# Database Entities

Resonance does not use the 
[Active Record](https://www.martinfowler.com/eaaCatalog/activeRecord.html) 
pattern. Instead, it uses read-only Database Entities (which are intentionally 
little more than basic PHP objects), hydrated directly from a query result.

This approach allows multiple easy optimizations, like 
{{docs/features/graphql/reusing-database-queries}} in 
{{docs/features/graphql/index}}, or just simple performance optimizations, like
fetching just the required columns from the database. 

# Usage

First, you have to define your data model (an instance of `DatabaseEntity`):

```php
<?php

namespace App\DatabaseEntity;

use Distantmagic\Resonance\DatabaseEntity;

final readonly class BlogPost extends DatabaseEntity
{
    public function __construct(
        public string $content,
        public string $slug,
        public string $title,
    ) {}
}
```

Then you need a query that hydrates the given model:

```php
<?php

namespace App\DatabaseQuery;

use App\DatabaseEntity\BlogPost;
use Generator;
use PDO;
use Distantmagic\Resonance\DatabaseQuery;
use Swoole\Database\PDOPool;

/**
 * @template-extends DatabaseQuery<Generator<int, BlogPost>>
 */
final readonly class SelectBlogPosts extends DatabaseQuery
{
    /**
     * @return Generator<int, BlogPost>
     */
    public function execute(): Generator
    {
        /**
         * @var Generator<int, array{
         *     content: string,
         *     slug: string,
         *     title: string,
         * }>
         */
        $blogPostsData = $this
            ->getConnection()
            ->prepare(<<<'SQL'
                SELECT
                    blog_posts.content,
                    blog_posts.slug,
                    blog_posts.title
                FROM blog_posts
            SQL)
            ->execute()
            ->yieldAssoc()
        ;

        foreach ($blogPostsData as $blogPostData) {
            yield new BlogPost(...$blogPostsData);
        }
    }

    public function isIterable(): bool
    {
        return true;
    }
}
```

Now, it's possible to reuse this query in {{docs/features/http/responders}},
{{docs/features/graphql/index}} resolvers, or pretty much anywhere in the 
application.
