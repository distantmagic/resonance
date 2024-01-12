<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\OpenAPIRouteSecurityRequirementExtractor;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\DoctrineEntityRouteParameter;
use Distantmagic\Resonance\Attribute\ExtractsOpenAPIRouteSecurityRequirement;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\CrudActionGateAggregate;
use Distantmagic\Resonance\CrudActionSubjectInterface;
use Distantmagic\Resonance\OpenAPIRouteSecurityRequirementExtractor;
use Distantmagic\Resonance\OpenAPISchemaSecurityRequirement;
use Distantmagic\Resonance\OpenAPISecuritySchema;
use Distantmagic\Resonance\SingletonCollection;
use LogicException;

/**
 * @template-extends OpenAPIRouteSecurityRequirementExtractor<DoctrineEntityRouteParameter>
 */
#[ExtractsOpenAPIRouteSecurityRequirement(DoctrineEntityRouteParameter::class)]
#[Singleton(collection: SingletonCollection::OpenAPIRouteSecurityRequirementExtractor)]
readonly class DoctrineEntityRouteParameterExtractor extends OpenAPIRouteSecurityRequirementExtractor
{
    public function __construct(
        private CrudActionGateAggregate $crudActionGateAggregate
    ) {}

    public function extractFromHttpControllerParameter(
        Attribute $attribute,
        string $parameterClass,
        string $parameterName,
    ): array {
        $securityRequirements = [];

        if (!is_a($parameterClass, CrudActionSubjectInterface::class, true)) {
            throw new LogicException(sprintf(
                'Parameter "%s" is not a "%s"',
                $parameterName,
                CrudActionSubjectInterface::class,
            ));
        }

        $requiresUser = $this
            ->crudActionGateAggregate
            ->crudActionGates
            ->get($parameterClass)
            ->requiresAuthenticatedUser($attribute->intent)
        ;

        if ($requiresUser) {
            $securityRequirements[] = new OpenAPISchemaSecurityRequirement(OpenAPISecuritySchema::Session);
        }

        return $securityRequirements;
    }
}
