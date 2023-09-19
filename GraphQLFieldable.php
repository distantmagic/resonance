<?php

declare(strict_types=1);

namespace Resonance;

interface GraphQLFieldable
{
    public function toGraphQLField(): array;
}
