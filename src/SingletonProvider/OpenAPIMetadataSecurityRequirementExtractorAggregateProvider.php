<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\ExtractsOpenAPIMetadataSecurityRequirement;
use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\OpenAPIMetadataSecurityRequirementExtractorAggregate;
use Distantmagic\Resonance\OpenAPIMetadataSecurityRequirementExtractorInterface;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<OpenAPIMetadataSecurityRequirementExtractorAggregate>
 */
#[RequiresSingletonCollection(SingletonCollection::OpenAPIMetadataSecurityRequirementExtractor)]
#[Singleton(provides: OpenAPIMetadataSecurityRequirementExtractorAggregate::class)]
final readonly class OpenAPIMetadataSecurityRequirementExtractorAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): OpenAPIMetadataSecurityRequirementExtractorAggregate
    {
        $openAPIMetadataSecurityRequirementExtractorAggregate = new OpenAPIMetadataSecurityRequirementExtractorAggregate();

        foreach ($this->collectExtractors($singletons) as $extractor) {
            $openAPIMetadataSecurityRequirementExtractorAggregate->extractors->put(
                $extractor->attribute->fromAttribute,
                $extractor->singleton,
            );
        }

        return $openAPIMetadataSecurityRequirementExtractorAggregate;
    }

    /**
     * @return iterable<SingletonAttribute<OpenAPIMetadataSecurityRequirementExtractorInterface,ExtractsOpenAPIMetadataSecurityRequirement>>
     */
    private function collectExtractors(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            OpenAPIMetadataSecurityRequirementExtractorInterface::class,
            ExtractsOpenAPIMetadataSecurityRequirement::class,
        );
    }
}
