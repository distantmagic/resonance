---
collections:
    - documents
layout: dm:document
parent: docs/features/database/swoole/index
title: Database Entities
description: >
    Learn how to map SQL queries results into reusable database entities.
---

# Database Entities

Resonance's database wrapper over Swoole's connection pools does not use the 
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
    const SQL = <<<'SQL'
        SELECT
            blog_posts.content,
            blog_posts.slug,
            blog_posts.title
        FROM blog_posts
    SQL;

    /**
     * @return Generator<int, BlogPost>
     */
    public function execute(): Generator
    {
        $stmt = $this->getConnection()->prepare(self::SQL);
        
        /**
         * @var list<array{
         *     content: string,
         *     slug: string,
         *     title: string,
         * }>
         */
        $blogPostsData = $stmt->execute()->fetchAllAssociative();

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
