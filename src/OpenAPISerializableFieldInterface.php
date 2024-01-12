<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface OpenAPISerializableFieldInterface
{
    public function toArray(
        OpenAPIReusableSchemaCollection $openAPIReusableSchemaCollection,
    ): array;
}
