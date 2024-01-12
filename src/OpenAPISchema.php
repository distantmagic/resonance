<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;

readonly class OpenAPISchema implements JsonSerializable
{
    public const VERSION = '3.1.0';

    public OpenAPISchemaInfo $openAPISchemaInfo;
    public OpenAPISchemaPaths $openAPISchemaPaths;
    public OpenAPISchemaServers $openAPISchemaServers;

    public function __construct(
        ApplicationConfiguration $applicationConfiguration,
        HttpControllerReflectionMethodCollection $httpControllerReflectionMethodCollection,
        OpenAPIConfiguration $openAPIConfiguration,
        OpenAPIMetadataResponseExtractorAggregate $openAPIMetadataResponseExtractorAggregate,
        OpenAPIMetadataSecurityRequirementExtractorAggregate $openAPIMetadataSecurityRequirementExtractorAggregate,
        OpenAPIPathItemCollection $openAPIPathItemCollection,
        OpenAPIRouteParameterExtractorAggregate $openAPIRouteParameterExtractorAggregate,
        OpenAPIRouteRequestBodyContentExtractorAggregate $openAPIRouteRequestBodyContentExtractorAggregate,
        OpenAPIRouteSecurityRequirementExtractorAggregate $openAPIRouteSecurityRequirementExtractorAggregate,
        private OpenAPISchemaComponentsSecuritySchemes $openAPISchemaComponentsSecuritySchemes,
        OpenAPISchemaSymbolInterface $openAPISchemaSymbol,
    ) {
        $this->openAPISchemaInfo = new OpenAPISchemaInfo($openAPIConfiguration);
        $this->openAPISchemaPaths = new OpenAPISchemaPaths(
            $httpControllerReflectionMethodCollection,
            $openAPIMetadataResponseExtractorAggregate,
            $openAPIMetadataSecurityRequirementExtractorAggregate,
            $openAPIPathItemCollection,
            $openAPIRouteParameterExtractorAggregate,
            $openAPIRouteRequestBodyContentExtractorAggregate,
            $openAPIRouteSecurityRequirementExtractorAggregate,
            $openAPISchemaSymbol,
        );
        $this->openAPISchemaServers = new OpenAPISchemaServers($applicationConfiguration);
    }

    public function jsonSerialize(): array
    {
        $openAPIReusableSchemaCollection = new OpenAPIReusableSchemaCollection();
        $openAPISchemaComponents = new OpenAPISchemaComponents($this->openAPISchemaComponentsSecuritySchemes);

        $infoSerialized = $this->openAPISchemaInfo->toArray($openAPIReusableSchemaCollection);
        $pathsSerialized = $this->openAPISchemaPaths->toArray($openAPIReusableSchemaCollection);
        $serversSerialized = $this->openAPISchemaServers->toArray($openAPIReusableSchemaCollection);

        return [
            'openapi' => self::VERSION,
            'info' => $infoSerialized,
            'servers' => $serversSerialized,
            'components' => $openAPISchemaComponents->toArray($openAPIReusableSchemaCollection),
            'paths' => $pathsSerialized,
        ];
    }
}
