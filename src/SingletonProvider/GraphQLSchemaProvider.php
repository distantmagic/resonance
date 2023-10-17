<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\ApplicationContext;
use Distantmagic\Resonance\Attribute\GraphQLRootField;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Environment;
use Distantmagic\Resonance\GraphQLFieldableInterface;
use Distantmagic\Resonance\GraphQLRootFieldType;
use Distantmagic\Resonance\ObjectType\RootFieldType;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use LogicException;

/**
 * @template-extends SingletonProvider<Schema>
 */
#[Singleton(
    provides: Schema::class,
    requiresCollection: SingletonCollection::GraphQLRootField,
)]
final readonly class GraphQLSchemaProvider extends SingletonProvider
{
    public function __construct(
        private ApplicationContext $applicationContext,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): Schema
    {
        // See docs on schema options:
        // https://webonyx.github.io/graphql-php/schema-definition/#configuration-options
        $schemaConfig = new SchemaConfig();

        $mutation = $this->getRootField($singletons, GraphQLRootFieldType::Mutation);

        if ($mutation) {
            $schemaConfig->setMutation($mutation);
        }

        $query = $this->getRootField($singletons, GraphQLRootFieldType::Query);

        if ($query) {
            $schemaConfig->setQuery($query);
        }

        $schema = new Schema($schemaConfig);

        if (Environment::Development === $this->applicationContext->environment) {
            $schema->assertValid();
        }

        return $schema;
    }

    /**
     * @return iterable<SingletonAttribute<GraphQLFieldableInterface,GraphQLRootField>>
     */
    private function collectQueryTypes(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            GraphQLFieldableInterface::class,
            GraphQLRootField::class,
        );
    }

    private function getRootField(
        SingletonContainer $singletons,
        GraphQLRootFieldType $type,
    ): ?RootFieldType {
        $fields = [];

        foreach ($this->collectQueryTypes($singletons) as $rootFieldType) {
            if ($rootFieldType->attribute->type === $type) {
                $name = $rootFieldType->attribute->name;

                if (array_key_exists($name, $fields)) {
                    throw new LogicException('Duplicate GraphQL schema Query key: '.$name.' in '.$rootFieldType->singleton::class);
                }

                $fields[$name] = $rootFieldType->singleton->toGraphQLField();
            }
        }

        if (empty($fields)) {
            return null;
        }

        return new RootFieldType($type, $fields);
    }
}
