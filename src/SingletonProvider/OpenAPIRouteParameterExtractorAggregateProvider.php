<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\ExtractsOpenAPIRouteParameter;
use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\OpenAPIRouteParameterExtractorAggregate;
use Distantmagic\Resonance\OpenAPIRouteParameterExtractorInterface;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<OpenAPIRouteParameterExtractorAggregate>
 */
#[RequiresSingletonCollection(SingletonCollection::OpenAPIRouteParameterExtractor)]
#[Singleton(provides: OpenAPIRouteParameterExtractorAggregate::class)]
final readonly class OpenAPIRouteParameterExtractorAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): OpenAPIRouteParameterExtractorAggregate
    {
        $openAPIRouteParameterExtractorAggregate = new OpenAPIRouteParameterExtractorAggregate();

        foreach ($this->collectExtractors($singletons) as $extractor) {
            $openAPIRouteParameterExtractorAggregate->extractors->put(
                $extractor->attribute->fromAttribute,
                $extractor->singleton,
            );
        }

        return $openAPIRouteParameterExtractorAggregate;
    }

    /**
     * @return iterable<SingletonAttribute<OpenAPIRouteParameterExtractorInterface,ExtractsOpenAPIRouteParameter>>
     */
    private function collectExtractors(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            OpenAPIRouteParameterExtractorInterface::class,
            ExtractsOpenAPIRouteParameter::class,
        );
    }
}
