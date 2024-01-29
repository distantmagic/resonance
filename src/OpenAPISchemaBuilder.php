<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpResponder\Json;

#[Singleton]
readonly class OpenAPISchemaBuilder
{
    public function __construct(
        private ApplicationConfiguration $applicationConfiguration,
        private HttpControllerReflectionMethodCollection $httpControllerReflectionMethodCollection,
        private JsonSerializer $jsonSerializer,
        private OpenAPIConfiguration $openAPIConfiguration,
        private OpenAPIMetadataResponseExtractorAggregate $openAPIMetadataResponseExtractorAggregate,
        private OpenAPIMetadataSecurityRequirementExtractorAggregate $openAPIMetadataSecurityRequirementExtractorAggregate,
        private OpenAPIPathItemCollection $openAPIPathItemCollection,
        private OpenAPIRouteParameterExtractorAggregate $openAPIRouteParameterExtractorAggregate,
        private OpenAPIRouteRequestBodyContentExtractorAggregate $openAPIRouteRequestBodyContentExtractorAggregate,
        private OpenAPIRouteSecurityRequirementExtractorAggregate $openAPIRouteSecurityRequirementExtractorAggregate,
        private OpenAPISchemaComponentsSecuritySchemes $openAPISchemaComponentsSecuritySchemes,
    ) {}

    public function buildSchema(OpenAPISchemaSymbolInterface $schemaSymbol): OpenAPISchema
    {
        return new OpenAPISchema(
            $this->applicationConfiguration,
            $this->httpControllerReflectionMethodCollection,
            $this->openAPIConfiguration,
            $this->openAPIMetadataResponseExtractorAggregate,
            $this->openAPIMetadataSecurityRequirementExtractorAggregate,
            $this->openAPIPathItemCollection,
            $this->openAPIRouteParameterExtractorAggregate,
            $this->openAPIRouteRequestBodyContentExtractorAggregate,
            $this->openAPIRouteSecurityRequirementExtractorAggregate,
            $this->openAPISchemaComponentsSecuritySchemes,
            $schemaSymbol,
        );
    }

    public function toJsonResponse(OpenAPISchemaSymbolInterface $schemaSymbol): Json
    {
        $schema = $this->buildSchema($schemaSymbol);
        $serialized = $this->jsonSerializer->serialize($schema);

        return new Json($serialized);
    }
}
