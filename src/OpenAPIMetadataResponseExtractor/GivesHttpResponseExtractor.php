<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\OpenAPIMetadataResponseExtractor;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\ExtractsOpenAPIMetadataResponse;
use Distantmagic\Resonance\Attribute\GivesHttpResponse;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\OpenAPIMetadataResponseExtractor;
use Distantmagic\Resonance\OpenAPISchemaResponse;
use Distantmagic\Resonance\SingletonCollection;
use ReflectionClass;

/**
 * @template-extends OpenAPIMetadataResponseExtractor<GivesHttpResponse>
 */
#[ExtractsOpenAPIMetadataResponse(GivesHttpResponse::class)]
#[Singleton(collection: SingletonCollection::OpenAPIMetadataResponseExtractor)]
readonly class GivesHttpResponseExtractor extends OpenAPIMetadataResponseExtractor
{
    public function extractFromHttpControllerMetadata(
        ReflectionClass $reflectionClass,
        Attribute $attribute,
    ): array {
        return [
            new OpenAPISchemaResponse(
                contentType: $attribute->contentType,
                description: $attribute->description,
                status: $attribute->status,
                jsonSchema: $attribute->jsonSchema,
                jsonSchemaName: sprintf(
                    'Response_%s',
                    str_replace('\\', '', $reflectionClass->getName()),
                ),
            ),
        ];
    }
}
