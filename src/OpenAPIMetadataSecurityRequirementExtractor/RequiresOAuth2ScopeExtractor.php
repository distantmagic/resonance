<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\OpenAPIMetadataSecurityRequirementExtractor;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\ExtractsOpenAPIMetadataSecurityRequirement;
use Distantmagic\Resonance\Attribute\RequiresOAuth2Scope;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\OpenAPIMetadataSecurityRequirementExtractor;
use Distantmagic\Resonance\OpenAPISchemaSecurityRequirement;
use Distantmagic\Resonance\OpenAPISecuritySchema;
use Distantmagic\Resonance\SingletonCollection;
use ReflectionClass;

/**
 * @template-extends OpenAPIMetadataSecurityRequirementExtractor<RequiresOAuth2Scope>
 */
#[ExtractsOpenAPIMetadataSecurityRequirement(RequiresOAuth2Scope::class)]
#[Singleton(collection: SingletonCollection::OpenAPIMetadataSecurityRequirementExtractor)]
readonly class RequiresOAuth2ScopeExtractor extends OpenAPIMetadataSecurityRequirementExtractor
{
    public function extractFromHttpControllerMetadata(
        ReflectionClass $reflectionClass,
        Attribute $attribute,
    ): array {
        return [
            new OpenAPISchemaSecurityRequirement(OpenAPISecuritySchema::Session),
            new OpenAPISchemaSecurityRequirement(OpenAPISecuritySchema::OAuth2, [
                $attribute->pattern->pattern,
            ]),
        ];
    }
}
