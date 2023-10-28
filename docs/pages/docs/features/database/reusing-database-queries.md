---
collections:
    - name: documents
      next: docs/features/database/database-entities
layout: dm:document
next: docs/features/database/database-entities
parent: docs/features/database/index
title: Reusing Database Queries
description: >
    Learn how to reuse the same database query in multiple places in your 
    application.
---

# Reusing Database Queries

## Writing Reusable Database Queries

You can wrap your query in the `DatabaseQuery` object. Then, it will be possible
to reuse that query in {{docs/features/http/responders}} or 
{{docs/features/graphql/index}}:

```php
<?php

namespace App\DatabaseQuery;

use App\DatabaseEntity\BlogPost;
use Distantmagic\Resonance\DatabaseConnectionPoolRepository;
use Distantmagic\Resonance\DatabaseQuery;

/**
 * @template-extends DatabaseQuery<null|BlogPost>
 */
final readonly class SelectBlogPostBySlug extends DatabaseQuery
{
    const SQL = <<<'SQL'
        SELECT
            blog_posts.content,
            blog_posts.slug,
            blog_posts.title,
        FROM blog_posts
        WHERE blog_posts.slug = :slug
        LIMIT 1
    SQL;

    public function __construct(
        DatabaseConnectionPoolRepository $databaseConnectionPoolRepository,
        private string $blogPostSlug,
    ) {
        parent::__construct($databaseConnectionPoolRepository);
    }

    /**
     * @return false|array{
     *     content: string,
     *     slug: string,
     *     title: string,
     * }
     */
    public function execute(): false|array
    {
        $stmt = $this->getConnection()->prepare(self::SQL);

        $stmt->bindValue('slug', $this->blogPostSlug);
        $stmt->execute();
        
        return $stmt->fetchAssociative();
    }
}
```

Using the query:

```php
<?php

use App\DatabaseQuery\SelectBlogPostBySlug;

$selectBlogPostBySlug = new SelectBlogPostBySlug(
    $databaseConnectionPoolRepository,
    'blog-post-slug',
);
$blogPost = $selectBlogPostBySlug->execute();
```

## Using Specific Connection Pool

You can choose a specific connection pool to execute the query by using
`$this->getConnection($connectionPoolName)`. For example:

```php
<?php

namespace App\DatabaseQuery;

use Distantmagic\Resonance\DatabaseQuery;
use Generator;

final readonly class SelectBlogPosts extends DatabaseQuery
{
    const SQL = <<<'SQL'
        SELECT blog_posts.title
        FROM blog_posts
    SQL;

    public function execute(): false|array
    {
        $stmt = $this->getConnection('readonly')->prepare(self::SQL)
        
        return $stmt->execute()->fetchAllAssociative();
    }
}
```
