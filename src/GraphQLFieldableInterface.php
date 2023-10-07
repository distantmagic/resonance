<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface GraphQLFieldableInterface
{
    public function toGraphQLField(): array;
}
