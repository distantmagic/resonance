<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\ObjectType;

use Distantmagic\Resonance\GraphQLRootFieldType;
use Distantmagic\Resonance\GraphQLSchemaRootFieldInterface;
use GraphQL\Type\Definition\ObjectType;

final class RootFieldType extends ObjectType implements GraphQLSchemaRootFieldInterface
{
    public function __construct(
        GraphQLRootFieldType $type,
        array $fields,
    ) {
        parent::__construct([
            'name' => $type->name,
            'fields' => $fields,
        ]);
    }
}
