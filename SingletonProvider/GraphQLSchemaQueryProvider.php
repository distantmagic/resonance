<?php

declare(strict_types=1);

namespace Resonance\SingletonProvider;

use LogicException;
use Resonance\Attribute\GraphQLRootQueryField;
use Resonance\Attribute\Singleton;
use Resonance\GraphQLFieldable;
use Resonance\GraphQLSchemaQueryInterface;
use Resonance\ObjectType\QueryType;
use Resonance\SingletonAttribute;
use Resonance\SingletonCollection;
use Resonance\SingletonContainer;
use Resonance\SingletonProvider;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

/**
 * @template-extends SingletonProvider<GraphQLSchemaQueryInterface>
 */
#[Singleton(
    provides: GraphQLSchemaQueryInterface::class,
    requiresCollection: SingletonCollection::GraphQLRootQueryField,
)]
final readonly class GraphQLSchemaQueryProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, ?ConsoleOutputInterface $output = null): GraphQLSchemaQueryInterface
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
     * @return iterable<SingletonAttribute<GraphQLFieldable,GraphQLRootQueryField>>
     */
    private function collectQueryTypes(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            GraphQLFieldable::class,
            GraphQLRootQueryField::class,
        );
    }
}
