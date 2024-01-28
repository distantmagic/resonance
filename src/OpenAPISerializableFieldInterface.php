<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TArrayResult of array
 */
interface OpenAPISerializableFieldInterface
{
    /**
     * @return TArrayResult
     */
    public function toArray(
        OpenAPIReusableSchemaCollection $openAPIReusableSchemaCollection,
    ): array;
}
