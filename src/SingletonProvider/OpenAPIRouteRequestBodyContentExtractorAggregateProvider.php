<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\ExtractsOpenAPIRouteRequestBody;
use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\OpenAPIRouteRequestBodyContentExtractorAggregate;
use Distantmagic\Resonance\OpenAPIRouteRequestBodyContentExtractorInterface;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<OpenAPIRouteRequestBodyContentExtractorAggregate>
 */
#[RequiresSingletonCollection(SingletonCollection::OpenAPIRouteRequestBodyContentExtractor)]
#[Singleton(provides: OpenAPIRouteRequestBodyContentExtractorAggregate::class)]
final readonly class OpenAPIRouteRequestBodyContentExtractorAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): OpenAPIRouteRequestBodyContentExtractorAggregate
    {
        $openAPIRouteRequestBodyContentExtractorAggregate = new OpenAPIRouteRequestBodyContentExtractorAggregate();

        foreach ($this->collectExtractors($singletons) as $extractor) {
            $openAPIRouteRequestBodyContentExtractorAggregate->extractors->put(
                $extractor->attribute->fromAttribute,
                $extractor->singleton,
            );
        }

        return $openAPIRouteRequestBodyContentExtractorAggregate;
    }

    /**
     * @return iterable<SingletonAttribute<OpenAPIRouteRequestBodyContentExtractorInterface,ExtractsOpenAPIRouteRequestBody>>
     */
    private function collectExtractors(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            OpenAPIRouteRequestBodyContentExtractorInterface::class,
            ExtractsOpenAPIRouteRequestBody::class,
        );
    }
}
