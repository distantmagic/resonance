<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use Ds\Set;
use LogicException;

readonly class OpenAPIReusableSchemaCollection
{
    /**
     * @var Map<JsonSchema,string>
     */
    public Map $references;

    /**
     * @var Set<string>
     */
    private Set $jsonSchemaNames;

    public function __construct()
    {
        $this->jsonSchemaNames = new Set();
        $this->references = new Map();
    }

    public function reuse(JsonSchema $jsonSchema, string $jsonSchemaName): JsonSchema
    {
        if (!$this->references->hasKey($jsonSchema)) {
            if ($this->jsonSchemaNames->contains($jsonSchemaName)) {
                throw new LogicException(sprintf(
                    'Non-unique json schema name: "%s"',
                    $jsonSchemaName,
                ));
            }

            $this->jsonSchemaNames->add($jsonSchemaName);
            $this->references->put($jsonSchema, $jsonSchemaName);
        }

        return new JsonSchema([
            '$ref' => sprintf(
                '#/components/schemas/%s',
                $this->references->get($jsonSchema),
            ),
        ]);
    }
}
