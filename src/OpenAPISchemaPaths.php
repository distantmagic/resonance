<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use LogicException;

readonly class OpenAPISchemaPaths implements OpenAPISerializableFieldInterface
{
    public function __construct(
        private HttpControllerReflectionMethodCollection $httpControllerReflectionMethodCollection,
        private OpenAPIMetadataResponseExtractorAggregate $openAPIMetadataResponseExtractorAggregate,
        private OpenAPIMetadataSecurityRequirementExtractorAggregate $openAPIMetadataSecurityRequirementExtractorAggregate,
        private OpenAPIPathItemCollection $openAPIPathItemCollection,
        private OpenAPIRouteParameterExtractorAggregate $openAPIRouteParameterExtractorAggregate,
        private OpenAPIRouteRequestBodyContentExtractorAggregate $openAPIRouteRequestBodyContentExtractorAggregate,
        private OpenAPIRouteSecurityRequirementExtractorAggregate $openAPIRouteSecurityRequirementExtractorAggregate,
        private OpenAPISchemaSymbolInterface $openAPISchemaSymbol,
    ) {}

    public function toArray(OpenAPIReusableSchemaCollection $openAPIReusableSchemaCollection): array
    {
        $paths = [];

        foreach ($this->openAPIPathItemCollection->pathItems as $pathItem) {
            if ($pathItem->openAPISchemaSymbol === $this->openAPISchemaSymbol) {
                if (!isset($paths[$pathItem->respondsToHttp->pattern])) {
                    $paths[$pathItem->respondsToHttp->pattern] = [];
                }

                $method = strtolower($pathItem->respondsToHttp->method->value);

                if (isset($paths[$pathItem->respondsToHttp->pattern][$method])) {
                    throw new LogicException(sprintf(
                        'Duplicate path definition: %s %s',
                        $method,
                        $pathItem->respondsToHttp->pattern,
                    ));
                }

                $operation = new OpenAPISchemaOperation(
                    $this->httpControllerReflectionMethodCollection,
                    $this->openAPIMetadataResponseExtractorAggregate,
                    $this->openAPIMetadataSecurityRequirementExtractorAggregate,
                    $pathItem,
                    $this->openAPIRouteParameterExtractorAggregate,
                    $this->openAPIRouteRequestBodyContentExtractorAggregate,
                    $this->openAPIRouteSecurityRequirementExtractorAggregate,
                );

                $paths[$pathItem->respondsToHttp->pattern][$method] = $operation->toArray($openAPIReusableSchemaCollection);
            }
        }

        return $paths;
    }
}
