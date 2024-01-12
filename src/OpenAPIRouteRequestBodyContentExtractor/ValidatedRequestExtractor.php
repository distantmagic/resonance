<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\OpenAPIRouteRequestBodyContentExtractor;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\ExtractsOpenAPIRouteRequestBody;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\ValidatedRequest;
use Distantmagic\Resonance\InputValidatorCollection;
use Distantmagic\Resonance\OpenAPIRouteRequestBodyContentExtractor;
use Distantmagic\Resonance\OpenAPISchemaRequestBodyContent;
use Distantmagic\Resonance\SingletonCollection;

/**
 * @template-extends OpenAPIRouteRequestBodyContentExtractor<ValidatedRequest>
 */
#[ExtractsOpenAPIRouteRequestBody(ValidatedRequest::class)]
#[Singleton(collection: SingletonCollection::OpenAPIRouteRequestBodyContentExtractor)]
readonly class ValidatedRequestExtractor extends OpenAPIRouteRequestBodyContentExtractor
{
    public function __construct(
        private InputValidatorCollection $inputValidatorCollection,
    ) {}

    public function extractFromHttpControllerParameter(
        Attribute $attribute,
        string $parameterClass,
        string $parameterName,
    ): array {
        return [
            new OpenAPISchemaRequestBodyContent(
                mimeType: 'application/x-www-form-urlencoded',
                jsonSchema: $this
                    ->inputValidatorCollection
                    ->inputValidators
                    ->get($attribute->validator)
                    ->jsonSchema,
                jsonSchemaName: str_replace('\\', '', $attribute->validator),
            ),
        ];
    }
}
