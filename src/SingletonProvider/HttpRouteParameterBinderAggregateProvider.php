<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\ProvidesRouteParameter;
use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpRouteParameterBinderAggregate;
use Distantmagic\Resonance\HttpRouteParameterBinderInterface;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<HttpRouteParameterBinderAggregate>
 */
#[RequiresSingletonCollection(SingletonCollection::HttpParameterBinder)]
#[Singleton(provides: HttpRouteParameterBinderAggregate::class)]
final readonly class HttpRouteParameterBinderAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): HttpRouteParameterBinderAggregate
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
