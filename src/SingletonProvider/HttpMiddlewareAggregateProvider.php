<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\PreprocessesHttpResponder;
use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpMiddlewareAggregate;
use Distantmagic\Resonance\HttpMiddlewareInterface;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use LogicException;

/**
 * @template-extends SingletonProvider<HttpMiddlewareAggregate>
 */
#[RequiresSingletonCollection(SingletonCollection::HttpMiddleware)]
#[Singleton(provides: HttpMiddlewareAggregate::class)]
final readonly class HttpMiddlewareAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): HttpMiddlewareAggregate
    {
        $httpMiddlewareAggregate = new HttpMiddlewareAggregate();

        foreach ($this->collectPreprocessors($singletons) as $preprocessorAttribute) {
            $attributeClassName = $preprocessorAttribute->attribute->attribute;

            foreach ($phpProjectFiles->findByAttribute($attributeClassName) as $subjectAttribute) {
                $responderClassName = $subjectAttribute->reflectionClass->getName();

                if (
                    !is_a($responderClassName, HttpInterceptableInterface::class, true)
                    && !is_a($responderClassName, HttpResponderInterface::class, true)
                ) {
                    throw new LogicException(sprintf(
                        '%s is not a %s nor a %s',
                        $subjectAttribute->reflectionClass->getName(),
                        HttpInterceptableInterface::class,
                        HttpResponderInterface::class,
                    ));
                }

                $httpMiddlewareAggregate->registerPreprocessor(
                    $preprocessorAttribute->singleton,
                    $responderClassName,
                    $subjectAttribute->attribute,
                    $preprocessorAttribute->attribute->priority,
                );
            }
        }

        $httpMiddlewareAggregate->sortPreprocessors();

        return $httpMiddlewareAggregate;
    }

    /**
     * @return iterable<SingletonAttribute<HttpMiddlewareInterface,PreprocessesHttpResponder>>
     */
    private function collectPreprocessors(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            HttpMiddlewareInterface::class,
            PreprocessesHttpResponder::class,
        );
    }
}
