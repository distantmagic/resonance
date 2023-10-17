<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Intercepts;
use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpInterceptorAggregate;
use Distantmagic\Resonance\HttpInterceptorInterface;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<HttpInterceptorAggregate>
 */
#[RequiresSingletonCollection(SingletonCollection::HttpInterceptor)]
#[Singleton(provides: HttpInterceptorAggregate::class)]
final readonly class HttpInterceptorAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): HttpInterceptorAggregate
    {
        $httpInterceptorAggregate = new HttpInterceptorAggregate();

        foreach ($this->collectInterceptors($singletons) as $interceptorAttribute) {
            $httpInterceptorAggregate->interceptors->put(
                $interceptorAttribute->attribute->responseClassName,
                $interceptorAttribute->singleton,
            );
        }

        return $httpInterceptorAggregate;
    }

    /**
     * @return iterable<SingletonAttribute<HttpInterceptorInterface,Intercepts>>
     */
    private function collectInterceptors(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            HttpInterceptorInterface::class,
            Intercepts::class,
        );
    }
}
