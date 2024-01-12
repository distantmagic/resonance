<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\ExtractsOpenAPIRouteSecurityRequirement;
use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\OpenAPIRouteSecurityRequirementExtractorAggregate;
use Distantmagic\Resonance\OpenAPIRouteSecurityRequirementExtractorInterface;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<OpenAPIRouteSecurityRequirementExtractorAggregate>
 */
#[RequiresSingletonCollection(SingletonCollection::OpenAPIRouteSecurityRequirementExtractor)]
#[Singleton(provides: OpenAPIRouteSecurityRequirementExtractorAggregate::class)]
final readonly class OpenAPIRouteSecurityRequirementExtractorAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): OpenAPIRouteSecurityRequirementExtractorAggregate
    {
        $openAPIRouteSecurityRequirementExtractorAggregate = new OpenAPIRouteSecurityRequirementExtractorAggregate();

        foreach ($this->collectExtractors($singletons) as $extractor) {
            $openAPIRouteSecurityRequirementExtractorAggregate->extractors->put(
                $extractor->attribute->fromAttribute,
                $extractor->singleton,
            );
        }

        return $openAPIRouteSecurityRequirementExtractorAggregate;
    }

    /**
     * @return iterable<SingletonAttribute<OpenAPIRouteSecurityRequirementExtractorInterface,ExtractsOpenAPIRouteSecurityRequirement>>
     */
    private function collectExtractors(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            OpenAPIRouteSecurityRequirementExtractorInterface::class,
            ExtractsOpenAPIRouteSecurityRequirement::class,
        );
    }
}
