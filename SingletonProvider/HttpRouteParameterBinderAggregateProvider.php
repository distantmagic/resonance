<?php

declare(strict_types=1);

namespace Resonance\SingletonProvider;

use Resonance\Attribute\ProvidesRouteParameter;
use Resonance\Attribute\Singleton;
use Resonance\HttpRouteParameterBinderAggregate;
use Resonance\HttpRouteParameterBinderInterface;
use Resonance\SingletonAttribute;
use Resonance\SingletonCollection;
use Resonance\SingletonContainer;
use Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<HttpRouteParameterBinderAggregate>
 */
#[Singleton(
    provides: HttpRouteParameterBinderAggregate::class,
    requiresCollection: SingletonCollection::HttpParameterBinder,
)]
final readonly class HttpRouteParameterBinderAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons): HttpRouteParameterBinderAggregate
    {
        $httpResponderAggregate = new HttpRouteParameterBinderAggregate();

        foreach ($this->collectResponders($singletons) as $httpResponderAttribute) {
            $httpResponderAggregate->httpRouteParameterBinders->put(
                $httpResponderAttribute->attribute->class,
                $httpResponderAttribute->singleton,
            );
        }

        return $httpResponderAggregate;
    }

    /**
     * @return iterable<SingletonAttribute<HttpRouteParameterBinderInterface,ProvidesRouteParameter>>
     */
    private function collectResponders(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            HttpRouteParameterBinderInterface::class,
            ProvidesRouteParameter::class,
        );
    }
}
