<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use GraphQL\Error\ClientAware;
use RuntimeException;

class GraphQLResolverException extends RuntimeException implements ClientAware
{
    public function isClientSafe(): bool
    {
        return true;
    }
}
