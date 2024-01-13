<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;

readonly class OpenAPIReusableSchemaCollection
{
    /**
     * @var Map<string,string>
     */
    public Map $hashes;

    /**
     * @var Map<JsonSchema,string>
     */
    public Map $references;

    public function __construct()
    {
        $this->hashes = new Map();
        $this->references = new Map();
    }

    public function reuse(JsonSchema $jsonSchema): JsonSchema
    {
        $hashed = $this->makeHash($jsonSchema);

        if (!$this->hashes->hasKey($hashed)) {
            $this->hashes->put($hashed, uniqid());
        }

        $schemaId = $this->hashes->get($hashed);

        $this->references->put($jsonSchema, $schemaId);

        return new JsonSchema([
            '$ref' => sprintf(
                '#/components/schemas/%s',
                $schemaId,
            ),
        ]);
    }

    private function makeHash(JsonSchema $jsonSchema): string
    {
        return serialize($jsonSchema->schema);
    }
}
