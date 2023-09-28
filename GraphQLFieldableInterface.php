<?php

declare(strict_types=1);

namespace Resonance;

interface GraphQLFieldableInterface
{
    public function toGraphQLField(): array;
}
