<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\ExtractsOpenAPIMetadataResponse;
use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\OpenAPIMetadataResponseExtractorAggregate;
use Distantmagic\Resonance\OpenAPIMetadataResponseExtractorInterface;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<OpenAPIMetadataResponseExtractorAggregate>
 */
#[RequiresSingletonCollection(SingletonCollection::OpenAPIMetadataResponseExtractor)]
#[Singleton(provides: OpenAPIMetadataResponseExtractorAggregate::class)]
final readonly class OpenAPIMetadataResponseExtractorAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): OpenAPIMetadataResponseExtractorAggregate
    {
        $openAPIMetadataResponseExtractorAggregate = new OpenAPIMetadataResponseExtractorAggregate();

        foreach ($this->collectExtractors($singletons) as $extractor) {
            $openAPIMetadataResponseExtractorAggregate->extractors->put(
                $extractor->attribute->fromAttribute,
                $extractor->singleton,
            );
        }

        return $openAPIMetadataResponseExtractorAggregate;
    }

    /**
     * @return iterable<SingletonAttribute<OpenAPIMetadataResponseExtractorInterface,ExtractsOpenAPIMetadataResponse>>
     */
    private function collectExtractors(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            OpenAPIMetadataResponseExtractorInterface::class,
            ExtractsOpenAPIMetadataResponse::class,
        );
    }
}
