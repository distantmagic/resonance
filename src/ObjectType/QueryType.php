<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\ObjectType;

use Distantmagic\Resonance\GraphQLSchemaQueryInterface;
use GraphQL\Type\Definition\ObjectType;

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
