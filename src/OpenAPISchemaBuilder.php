<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;

#[Singleton]
readonly class OpenAPISchemaBuilder
{
    public function __construct(
        private ApplicationConfiguration $applicationConfiguration,
        private OpenAPIConfiguration $openAPIConfiguration,
        private OpenAPIPathItemCollection $openAPIPathItemCollection,
        private OpenAPISchemaComponents $openAPISchemaComponents,
    ) {}

    public function buildSchema(OpenAPISchemaSymbolInterface $schemaSymbol): OpenAPISchema
    {
        return new OpenAPISchema(
            $this->applicationConfiguration,
            $this->openAPIConfiguration,
            $this->openAPIPathItemCollection,
            $this->openAPISchemaComponents,
            $schemaSymbol,
        );
    }
}
