<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\ResolvesHttpControllerParameter;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpControllerParameterResolverAggregate;
use Distantmagic\Resonance\HttpControllerParameterResolverInterface;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<HttpControllerParameterResolverAggregate>
 */
#[Singleton(
    provides: HttpControllerParameterResolverAggregate::class,
    requiresCollection: SingletonCollection::HttpControllerParameterResolver,
)]
final readonly class HttpControllerParameterResolverAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): HttpControllerParameterResolverAggregate
    {
        $httpControllerParameterResolverAggregate = new HttpControllerParameterResolverAggregate();

        foreach ($this->collectResponders($singletons) as $httpResponderAttribute) {
            $httpControllerParameterResolverAggregate->resolvers->put(
                $httpResponderAttribute->attribute->attribute,
                $httpResponderAttribute->singleton,
            );
        }

        return $httpControllerParameterResolverAggregate;
    }

    /**
     * @return iterable<SingletonAttribute<HttpControllerParameterResolverInterface,ResolvesHttpControllerParameter>>
     */
    private function collectResponders(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            HttpControllerParameterResolverInterface::class,
            ResolvesHttpControllerParameter::class,
        );
    }
}
