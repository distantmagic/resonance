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
        OpenAPIPathItemCollection $openAPIPathItemCollection,
        OpenAPIRouteParameterExtractorAggregate $openAPIRouteParameterExtractorAggregate,
        private OpenAPISchemaComponents $openAPISchemaComponents,
        OpenAPISchemaSymbolInterface $openAPISchemaSymbol,
    ) {
        $this->openAPISchemaInfo = new OpenAPISchemaInfo($openAPIConfiguration);
        $this->openAPISchemaPaths = new OpenAPISchemaPaths(
            $httpControllerReflectionMethodCollection,
            $openAPIPathItemCollection,
            $openAPIRouteParameterExtractorAggregate,
            $openAPISchemaSymbol,
        );
        $this->openAPISchemaServers = new OpenAPISchemaServers($applicationConfiguration);
    }

    public function jsonSerialize(): array
    {
        return [
            'openapi' => self::VERSION,
            'info' => $this->openAPISchemaInfo,
            'servers' => $this->openAPISchemaServers,
            'components' => $this->openAPISchemaComponents,
            'paths' => $this->openAPISchemaPaths,
        ];
    }
}
