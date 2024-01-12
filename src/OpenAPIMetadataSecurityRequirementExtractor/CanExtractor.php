<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\OpenAPIMetadataSecurityRequirementExtractor;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\Can;
use Distantmagic\Resonance\Attribute\ExtractsOpenAPIMetadataSecurityRequirement;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\OpenAPIMetadataSecurityRequirementExtractor;
use Distantmagic\Resonance\OpenAPISchemaSecurityRequirement;
use Distantmagic\Resonance\OpenAPISecuritySchema;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SiteActionGateAggregate;
use ReflectionClass;

/**
 * @template-extends OpenAPIMetadataSecurityRequirementExtractor<Can>
 */
#[ExtractsOpenAPIMetadataSecurityRequirement(Can::class)]
#[Singleton(collection: SingletonCollection::OpenAPIMetadataSecurityRequirementExtractor)]
readonly class CanExtractor extends OpenAPIMetadataSecurityRequirementExtractor
{
    public function __construct(
        private SiteActionGateAggregate $siteActionGateAggregate,
    ) {}

    public function extractFromHttpControllerMetadata(
        ReflectionClass $reflectionClass,
        Attribute $attribute,
    ): array {
        $securityRequirements = [];

        $requiresUser = $this
            ->siteActionGateAggregate
            ->siteActionGates
            ->get($attribute->siteAction)
            ->requiresAuthenticatedUser()
        ;

        if ($requiresUser) {
            $securityRequirements[] = new OpenAPISchemaSecurityRequirement(OpenAPISecuritySchema::Session);
        }

        return $securityRequirements;
    }
}
