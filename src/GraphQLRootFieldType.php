<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum GraphQLRootFieldType
{
    case Mutation;
    case Query;
}
