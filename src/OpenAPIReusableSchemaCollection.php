<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use RuntimeException;

readonly class OpenAPIReusableSchemaCollection
{
    /**
     * @var Map<non-empty-string,non-empty-string>
     */
    public Map $hashes;

    /**
     * @var Map<JsonSchema,non-empty-string>
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

    /**
     * @return non-empty-string
     */
    private function makeHash(JsonSchema $jsonSchema): string
    {
        $serialized = serialize($jsonSchema->schema);

        if (empty($serialized)) {
            throw new RuntimeException('Unable to serialize JsonSchema');
        }

        return $serialized;
    }
}
