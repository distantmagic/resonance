<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\HandlesMiddlewareAttribute;
use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpMiddlewareAggregate;
use Distantmagic\Resonance\HttpMiddlewareInterface;
use Distantmagic\Resonance\HttpResponderCollection;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<HttpMiddlewareAggregate>
 */
#[RequiresSingletonCollection(SingletonCollection::HttpMiddleware)]
#[Singleton(provides: HttpMiddlewareAggregate::class)]
final readonly class HttpMiddlewareAggregateProvider extends SingletonProvider
{
    public function __construct(
        private HttpResponderCollection $httpResponderCollection,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): HttpMiddlewareAggregate
    {
        $httpMiddlewareAggregate = new HttpMiddlewareAggregate();

        foreach ($this->collectMiddlewares($singletons) as $middlewareAttribute) {
            $attributeClassName = $middlewareAttribute->attribute->attribute;

            foreach ($this->httpResponderCollection->httpResponders as $httpResponderWithAtribute) {
                $attribute = $httpResponderWithAtribute
                    ->getReflectionAttributeManager()
                    ->findAttribute($attributeClassName)
                ;

                if (!$attribute) {
                    continue;
                }

                $httpMiddlewareAggregate->registerPreprocessor(
                    $attribute,
                    $middlewareAttribute->singleton,
                    $httpResponderWithAtribute->httpResponder,
                    $middlewareAttribute->attribute->priority,
                );
            }
        }

        $httpMiddlewareAggregate->sortPreprocessors();

        return $httpMiddlewareAggregate;
    }

    /**
     * @return iterable<SingletonAttribute<HttpMiddlewareInterface,HandlesMiddlewareAttribute>>
     */
    private function collectMiddlewares(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            HttpMiddlewareInterface::class,
            HandlesMiddlewareAttribute::class,
        );
    }
}
