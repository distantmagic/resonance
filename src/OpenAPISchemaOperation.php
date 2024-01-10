<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use JsonSerializable;

readonly class OpenAPISchemaOperation implements JsonSerializable
{
    public function __construct(private OpenAPIPathItem $openAPIPathItem) {}

    public function jsonSerialize(): array
    {
        $operation = [];

        if (isset($this->openAPIPathItem->respondsToHttp->description)) {
            $operation['description'] = $this->openAPIPathItem->respondsToHttp->description;
        }

        if (isset($this->openAPIPathItem->respondsToHttp->summary)) {
            $operation['summary'] = $this->openAPIPathItem->respondsToHttp->summary;
        }

        $operation['operationId'] = $this->generateOperationId();

        $security = $this->serializeSecurity();

        if (!empty($security)) {
            $operation['security'] = $security;
        }

        return $operation;
    }

    private function generateOperationId(): string
    {
        return sprintf(
            '%s%s',
            ucfirst(strtolower($this->openAPIPathItem->respondsToHttp->method->value)),
            str_replace('\\', '', $this->openAPIPathItem->reflectionClass->getName()),
        );
    }

    private function serializeRequiredOAuth2Scopes()
    {
        $scopes = [];

        foreach ($this->openAPIPathItem->requiredOAuth2Scopes as $scope) {
            $scopes[] = $scope->pattern->pattern;
        }

        return $scopes;
    }

    private function serializeSecurity(): array
    {
        $security = [];

        if (!$this->openAPIPathItem->requiredOAuth2Scopes->isEmpty()) {
            $security[OpenAPISecuritySchema::OAuth2->name] = $this->serializeRequiredOAuth2Scopes();
        }

        return $security;
    }
}
