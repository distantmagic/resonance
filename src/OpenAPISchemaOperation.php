<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\GivesHttpResponse;
use Distantmagic\Resonance\HttpResponder\HttpController;
use LogicException;
use ReflectionAttribute;

readonly class OpenAPISchemaOperation implements OpenAPISerializableFieldInterface
{
    private HttpControllerReflectionMethod $httpControllerReflectionMethod;

    public function __construct(
        HttpControllerReflectionMethodCollection $httpControllerReflectionMethodCollection,
        private OpenAPIMetadataResponseExtractorAggregate $openAPIMetadataResponseExtractorAggregate,
        private OpenAPIMetadataSecurityRequirementExtractorAggregate $openAPIMetadataSecurityRequirementExtractorAggregate,
        private OpenAPIPathItem $openAPIPathItem,
        private OpenAPIRouteParameterExtractorAggregate $openAPIRouteParameterExtractorAggregate,
        private OpenAPIRouteRequestBodyContentExtractorAggregate $openAPIRouteRequestBodyContentExtractorAggregate,
        private OpenAPIRouteSecurityRequirementExtractorAggregate $openAPIRouteSecurityRequirementExtractorAggregate,
    ) {
        $httpResponderClass = $this->openAPIPathItem->reflectionClass->getName();

        if (!is_a($httpResponderClass, HttpController::class, true)) {
            throw new LogicException(sprintf(
                'OpenAPI parameters can only be inferred from "%s", got "%s"',
                HttpController::class,
                $httpResponderClass,
            ));
        }

        $this->httpControllerReflectionMethod = $httpControllerReflectionMethodCollection
            ->reflectionMethods
            ->get($httpResponderClass)
        ;
    }

    public function toArray(OpenAPIReusableSchemaCollection $openAPIReusableSchemaCollection): array
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

        $requestBodyContents = $this->serializeRequestBodyContents($openAPIReusableSchemaCollection);

        if (!empty($requestBodyContents)) {
            $operation['requestBody'] = [
                'content' => $requestBodyContents,
            ];
        }

        $security = $this->serializeSecurity($openAPIReusableSchemaCollection);

        if (!empty($security)) {
            $operation['security'] = $security;
        }

        $operation['responses'] = $this->serializeResponses($openAPIReusableSchemaCollection);

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

    private function serializeParameters(): array
    {
        $parameters = [];

        foreach ($this->httpControllerReflectionMethod->parameters as $reflectionMethodParameter) {
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

    private function serializeRequestBodyContents(
        OpenAPIReusableSchemaCollection $openAPIReusableSchemaCollection,
    ): array {
        $requestBodyContents = [];

        foreach ($this->httpControllerReflectionMethod->parameters as $reflectionMethodParameter) {
            if ($reflectionMethodParameter->attribute) {
                $parameterResolvedValue = $this
                    ->openAPIRouteRequestBodyContentExtractorAggregate
                    ->extractFromHttpControllerParameter(
                        $reflectionMethodParameter->attribute,
                        $reflectionMethodParameter->className,
                        $reflectionMethodParameter->name,
                    )
                ;

                foreach ($parameterResolvedValue as $requestBodyContent) {
                    if (isset($requestBodyContents[$requestBodyContent->mimeType])) {
                        throw new LogicException(sprintf(
                            'Ambiguous request body resolution in "%s"',
                            $reflectionMethodParameter->className,
                        ));
                    }

                    $requestBodyContents[$requestBodyContent->mimeType] = $requestBodyContent->toArray($openAPIReusableSchemaCollection);
                }
            }
        }

        return $requestBodyContents;
    }

    private function serializeResponses(OpenAPIReusableSchemaCollection $openAPIReusableSchemaCollection): array
    {
        $httpControllerReflectionClass = $this->httpControllerReflectionMethod->reflectionClass;
        $responses = [];

        foreach ($httpControllerReflectionClass->getAttributes(Attribute::class, ReflectionAttribute::IS_INSTANCEOF) as $reflectionAttribute) {
            $attribute = $reflectionAttribute->newInstance();
            $extractedResponses = $this
                ->openAPIMetadataResponseExtractorAggregate
                ->extractFromHttpControllerMetadata($httpControllerReflectionClass, $attribute)
            ;

            foreach ($extractedResponses as $response) {
                $responses[$response->status] = $response->toArray($openAPIReusableSchemaCollection);
            }
        }

        if (empty($responses)) {
            throw new LogicException(sprintf(
                'Unable to infer response types from "%s". To resolve that you can add "%s" attributes',
                $httpControllerReflectionClass->getName(),
                GivesHttpResponse::class,
            ));
        }

        return $responses;
    }

    private function serializeSecurity(OpenAPIReusableSchemaCollection $openAPIReusableSchemaCollection): array
    {
        $mergedSecurityRequirements = [];

        foreach ($this->httpControllerReflectionMethod->parameters as $reflectionMethodParameter) {
            if ($reflectionMethodParameter->attribute) {
                $extractedSecurityRequirements = $this
                    ->openAPIRouteSecurityRequirementExtractorAggregate
                    ->extractFromHttpControllerParameter(
                        $reflectionMethodParameter->attribute,
                        $reflectionMethodParameter->className,
                        $reflectionMethodParameter->name,
                    )
                ;

                foreach ($extractedSecurityRequirements as $securityRequirement) {
                    $mergedSecurityRequirements = array_merge_recursive(
                        $mergedSecurityRequirements,
                        $securityRequirement->toArray($openAPIReusableSchemaCollection),
                    );
                }
            }
        }

        $httpControllerReflectionClass = $this->httpControllerReflectionMethod->reflectionClass;

        foreach ($httpControllerReflectionClass->getAttributes(Attribute::class, ReflectionAttribute::IS_INSTANCEOF) as $reflectionAttribute) {
            $attribute = $reflectionAttribute->newInstance();
            $extractedSecurityRequirements = $this
                ->openAPIMetadataSecurityRequirementExtractorAggregate
                ->extractFromHttpControllerMetadata($httpControllerReflectionClass, $attribute)
            ;

            foreach ($extractedSecurityRequirements as $securityRequirement) {
                $mergedSecurityRequirements = array_merge_recursive(
                    $mergedSecurityRequirements,
                    $securityRequirement->toArray($openAPIReusableSchemaCollection),
                );
            }
        }

        $securityRequirements = [];

        /**
         * @var array<string> $mergedSecurityRequirement
         */
        foreach ($mergedSecurityRequirements as $securitySchemeName => $mergedSecurityRequirement) {
            $securityRequirements[] = [
                $securitySchemeName => $mergedSecurityRequirement,
            ];
        }

        return $securityRequirements;
    }
}
