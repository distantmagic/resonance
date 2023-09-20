<?php

declare(strict_types=1);

namespace Resonance\ObjectType;

use GraphQL\Type\Definition\ObjectType;
use Resonance\GraphQLSchemaQueryInterface;

final class QueryType extends ObjectType implements GraphQLSchemaQueryInterface
{
    public function __construct(array $fields)
    {
        parent::__construct([
            'name' => 'Query',
            'fields' => $fields,
        ]);
    }
}
