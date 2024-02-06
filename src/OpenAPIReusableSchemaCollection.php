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
     * @var Map<JsonSchemableInterface,non-empty-string>
     */
    public Map $references;

    public function __construct()
    {
        $this->hashes = new Map();
        $this->references = new Map();
    }

    public function reuse(JsonSchemableInterface $jsonSchemable): array
    {
        $hashed = $this->makeHash($jsonSchemable);

        if (!$this->hashes->hasKey($hashed)) {
            $this->hashes->put($hashed, uniqid());
        }

        $schemaId = $this->hashes->get($hashed);

        $this->references->put($jsonSchemable, $schemaId);

        return [
            '$ref' => sprintf(
                '#/components/schemas/%s',
                $schemaId,
            ),
        ];
    }

    /**
     * @return non-empty-string
     */
    private function makeHash(JsonSchemableInterface $jsonSchemable): string
    {
        $serialized = serialize($jsonSchemable->toJsonSchema());

        if (empty($serialized)) {
            throw new RuntimeException('Unable to serialize JsonSchemableInterface');
        }

        return $serialized;
    }
}
