<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;
use LogicException;

readonly class OpenAPISchemaPaths implements JsonSerializable
{
    public function __construct(
        private HttpControllerReflectionMethodCollection $httpControllerReflectionMethodCollection,
        private OpenAPIPathItemCollection $openAPIPathItemCollection,
        private OpenAPIRouteParameterExtractorAggregate $openAPIRouteParameterExtractorAggregate,
        private OpenAPISchemaSymbolInterface $openAPISchemaSymbol,
    ) {}

    public function jsonSerialize(): array
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

                $paths[$pathItem->respondsToHttp->pattern][$method] = new OpenAPISchemaOperation(
                    $this->httpControllerReflectionMethodCollection,
                    $pathItem,
                    $this->openAPIRouteParameterExtractorAggregate,
                );
            }
        }

        return $paths;
    }
}
