<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use GraphQL\Type\Definition\CompositeType;
use GraphQL\Type\Definition\HasFieldsType;
use GraphQL\Type\Definition\ImplementingType;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\NullableType;
use GraphQL\Type\Definition\OutputType;

interface GraphQLSchemaRootFieldInterface extends CompositeType, HasFieldsType, ImplementingType, NamedType, NullableType, OutputType {}
