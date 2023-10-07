<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\GraphQLRootQueryField;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\GraphQLFieldableInterface;
use Distantmagic\Resonance\GraphQLSchemaQueryInterface;
use Distantmagic\Resonance\ObjectType\QueryType;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use LogicException;

/**
 * @template-extends SingletonProvider<GraphQLSchemaQueryInterface>
 */
#[Singleton(
    provides: GraphQLSchemaQueryInterface::class,
    requiresCollection: SingletonCollection::GraphQLRootQueryField,
)]
final readonly class GraphQLSchemaQueryProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): GraphQLSchemaQueryInterface
    {
        $fields = [];

        foreach ($this->collectQueryTypes($singletons) as $queryType) {
            $name = $queryType->attribute->name;

            if (array_key_exists($name, $fields)) {
                throw new LogicException('Duplicate GraphQL schema Query key: '.$name.' in '.$queryType->singleton::class);
            }

            $fields[$name] = $queryType->singleton->toGraphQLField();
        }

        return new QueryType($fields);
    }

    /**
     * @return iterable<SingletonAttribute<GraphQLFieldableInterface,GraphQLRootQueryField>>
     */
    private function collectQueryTypes(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            GraphQLFieldableInterface::class,
            GraphQLRootQueryField::class,
        );
    }
}
