<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Environment;
use Distantmagic\Resonance\GraphQLSchemaQueryInterface;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use LogicException;

/**
 * @template-extends SingletonProvider<Schema>
 */
#[Singleton(provides: Schema::class)]
final readonly class GraphQLSchemaProvider extends SingletonProvider
{
    public function __construct(private GraphQLSchemaQueryInterface $query) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): Schema
    {
        if (!($this->query instanceof ObjectType)) {
            throw new LogicException('Expected GraphQLSchemaQueryInterface to be an instance of '.ObjectType::class);
        }

        // See docs on schema options:
        // https://webonyx.github.io/graphql-php/schema-definition/#configuration-options
        $schema = new Schema([
            'query' => $this->query,
        ]);

        if (DM_APP_ENV === Environment::Development) {
            $schema->assertValid();
        }

        return $schema;
    }
}
