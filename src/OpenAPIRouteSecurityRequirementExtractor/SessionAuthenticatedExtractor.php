<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\OpenAPIRouteSecurityRequirementExtractor;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\ExtractsOpenAPIRouteSecurityRequirement;
use Distantmagic\Resonance\Attribute\SessionAuthenticated;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\OpenAPIRouteSecurityRequirementExtractor;
use Distantmagic\Resonance\OpenAPISchemaSecurityRequirement;
use Distantmagic\Resonance\OpenAPISecuritySchema;
use Distantmagic\Resonance\SingletonCollection;

/**
 * @template-extends OpenAPIRouteSecurityRequirementExtractor<SessionAuthenticated>
 */
#[ExtractsOpenAPIRouteSecurityRequirement(SessionAuthenticated::class)]
#[Singleton(collection: SingletonCollection::OpenAPIRouteSecurityRequirementExtractor)]
readonly class SessionAuthenticatedExtractor extends OpenAPIRouteSecurityRequirementExtractor
{
    public function extractFromHttpControllerParameter(
        Attribute $attribute,
        string $parameterClass,
        string $parameterName,
    ): array {
        return [
            new OpenAPISchemaSecurityRequirement(OpenAPISecuritySchema::Session),
        ];
    }
}
