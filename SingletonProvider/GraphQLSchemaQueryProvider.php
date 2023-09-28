<?php

declare(strict_types=1);

namespace Resonance\SingletonProvider;

use LogicException;
use Resonance\Attribute\GraphQLRootQueryField;
use Resonance\Attribute\Singleton;
use Resonance\GraphQLFieldableInterface;
use Resonance\GraphQLSchemaQueryInterface;
use Resonance\ObjectType\QueryType;
use Resonance\SingletonAttribute;
use Resonance\SingletonCollection;
use Resonance\SingletonContainer;
use Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<GraphQLSchemaQueryInterface>
 */
#[Singleton(
    provides: GraphQLSchemaQueryInterface::class,
    requiresCollection: SingletonCollection::GraphQLRootQueryField,
)]
final readonly class GraphQLSchemaQueryProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons): GraphQLSchemaQueryInterface
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
