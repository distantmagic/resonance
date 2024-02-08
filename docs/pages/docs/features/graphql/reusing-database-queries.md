---
collections: 
    - name: documents
      next: docs/features/graphql/standalone-promise-adapter
layout: dm:document
next: docs/features/graphql/standalone-promise-adapter
parent: docs/features/graphql/index
title: Reusing Database Queries
description: >
    Optimize GraphQL responses by reusing SQL queries, preventing redundant 
    database queries for requested data.
---

# Reusing Database Queries

When dealing with GraphQL responses, it's common to encounter scenarios where 
the same data is requested multiple times, like fetching blog post 
authors and the currently authenticated user simultaneously.

Resonance provides a mechanism to reuse specific queries to optimize database 
performance and avoid redundant queries. This feature relies on
{{docs/features/database/swoole/database-entities}} and the implementation of 
a specific interface for GraphQL.

## Example

Let's create a {{docs/features/graphql/index}} root field that retrieves a blog 
post based on a specific slug.

### Database Entity

First, create a database model for the blog post:

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

### Database Query

Next, implement a database query to hydrate the `BlogPost` model. Ensure that 
this query implements the 
<code>GraphQL<wbr>ReusableDatabaseQuery<wbr>Interface</code> and defines a 
unique identifier for the query using the `reusableQueryId` method:

```php
<?php

namespace App\DatabaseQuery;

use App\DatabaseEntity\BlogPost;
use Distantmagic\Resonance\DatabaseConnectionPoolRepository;
use Distantmagic\Resonance\DatabaseQuery;
use Distantmagic\Resonance\GraphQLReusableDatabaseQueryInterfacep;

/**
 * @template-extends DatabaseQuery<null|BlogPost>
 */
final readonly class SelectBlogPostBySlug 
    extends DatabaseQuery 
    implements GraphQLReusableDatabaseQueryInterface
{
    public function __construct(
        DatabaseConnectionPoolRepository $databaseConnectionPoolRepository,
        private string $blogPostSlug,
    ) {
        parent::__construct($databaseConnectionPoolRepository);
    }

    public function execute(): ?BlogPost
    {
        /**
         * @var null|array{
         *     content: string,
         *     slug: string,
         *     title: string,
         * }
         */
        $blogPostData = $this
            ->getConnection()
            ->prepare(<<<'SQL'
                SELECT
                    blog_posts.content,
                    blog_posts.slug,
                    blog_posts.title
                FROM blog_posts
                WHERE blog_posts.slug = :slug
                LIMIT 1
            SQL)
            ->bindValue('slug', $this->blogPostSlug)
            ->execute()
            ->first()
        ;

        if (!$blogPostData) {
            return null;
        }

        return new BlogPost(...$blogPostData);
    }

    public function reusableQueryId(): string
    {
        return $this->blogPostSlug;
    }
}
```

### GraphQL Field

Now, let's add a GraphQL root field for the blog post:

```php
<?php

namespace App\GraphQLRootField;

use App\DatabaseQuery\SelectBlogPostBySlug;
use App\ObjectType\BlogPostType;
use GraphQL\Type\Definition\Type;
use Distantmagic\Resonance\Attribute\GraphQLRootField;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\DatabaseConnectionPoolRepository;
use Distantmagic\Resonance\GraphQLFieldableInterface;
use Distantmagic\Resonance\GraphQLReusableDatabaseQueryInterface;
use Distantmagic\Resonance\SingletonCollection;

#[GraphQLRootField(name: 'blogPost')]
#[Singleton(collection: SingletonCollection::GraphQLRootField)]
final readonly class BlogPost implements GraphQLFieldableInterface
{
    public function __construct(
        private DatabaseConnectionPoolRepository $databaseConnectionPoolRepository,
        private BlogPostType $blogPostType,
    ) {}

    /**
     * @param array{ slug: string } $args
     *
     * @psalm-suppress UnusedParam
     */
    public function resolve(null $rootValue, array $args): GraphQLReusableDatabaseQueryInterface
    {
        return new SelectBlogPostBySlug(
            $this->databaseConnectionPoolRepository,
            $args['slug'],
        );
    }

    public function toGraphQLField(): array
    {
        return [
            'type' => $this->blogPostType,
            'args' => [
                'slug' => [
                    'type' => Type::nonNull(Type::string()),
                ],
            ],
            'resolve' => $this->resolve(...),
        ];
    }
}
```

### Querying GraphQL

With this setup, a GraphQL query will issue the database query only twice 
(instead of four times) for both "foo" and "bar" slugs:

```graphql
query Query {
    blogPost1: blogPost(slug: "foo") {
        title
    }
    blogPost2: blogPost(slug: "foo") {
        title
    }
    blogPost3: blogPost(slug: "foo") {
        title
    }
    blogPost4: blogPost(slug: "bar") {
        title
    }
}
```

This example demonstrates how merging database queries works, especially when 
dealing with complex GraphQL queries containing nested resources.
