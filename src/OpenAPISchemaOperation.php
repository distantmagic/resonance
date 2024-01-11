<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\HttpResponder\HttpController;
use JsonSerializable;
use LogicException;

readonly class OpenAPISchemaOperation implements JsonSerializable
{
    public function __construct(
        private HttpControllerReflectionMethodCollection $httpControllerReflectionMethodCollection,
        private OpenAPIPathItem $openAPIPathItem,
        private OpenAPIRouteParameterExtractorAggregate $openAPIRouteParameterExtractorAggregate,
    ) {}

    public function jsonSerialize(): array
    {
        $operation = [];

        $operation['operationId'] = $this->generateOperationId();

        if (isset($this->openAPIPathItem->respondsToHttp->description)) {
            $operation['description'] = $this->openAPIPathItem->respondsToHttp->description;
        }

        if (isset($this->openAPIPathItem->respondsToHttp->summary)) {
            $operation['summary'] = $this->openAPIPathItem->respondsToHttp->summary;
        }

        $parameters = $this->serializeParameters();

        if (!empty($parameters)) {
            $operation['parameters'] = $parameters;
        }

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

    // private function serializeRequiredOAuth2Scopes(): array
    // {
    //     $scopes = [];

    //     // foreach ($this->openAPIPathItem->requiredOAuth2Scopes as $scope) {
    //     //     $scopes[] = $scope->pattern->pattern;
    //     // }

    //     return $scopes;
    // }

    private function serializeParameters(): array
    {
        $parameters = [];

        $httpResponderClass = $this->openAPIPathItem->reflectionClass->getName();

        if (!is_a($httpResponderClass, HttpController::class, true)) {
            throw new LogicException(sprintf(
                'OpenAPI parameters can only be inferred from "%s", got "%s"',
                HttpController::class,
                $httpResponderClass,
            ));
        }

        $httpControllerReflectionMethod = $this
            ->httpControllerReflectionMethodCollection
            ->reflectionMethods
            ->get($httpResponderClass)
        ;

        foreach ($httpControllerReflectionMethod->parameters as $reflectionMethodParameter) {
            if ($reflectionMethodParameter->attribute) {
                $extractedParameters = $this
                    ->openAPIRouteParameterExtractorAggregate
                    ->extractFromHttpControllerParameter(
                        $reflectionMethodParameter->attribute,
                        $reflectionMethodParameter->className,
                        $reflectionMethodParameter->name,
                    )
                ;

                foreach ($extractedParameters as $parameter) {
                    $parameters[] = $parameter;
                }
            }
        }

        return $parameters;
    }

    private function serializeSecurity(): array
    {
        return [];

        // if (!$this->openAPIPathItem->requiredOAuth2Scopes->isEmpty()) {
        //     $security[OpenAPISecuritySchema::OAuth2->name] = $this->serializeRequiredOAuth2Scopes();
        // }
    }
}
