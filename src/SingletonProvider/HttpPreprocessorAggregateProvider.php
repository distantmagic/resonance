<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\PreprocessesHttpResponder;
use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpPreprocessorAggregate;
use Distantmagic\Resonance\HttpPreprocessorInterface;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use LogicException;

/**
 * @template-extends SingletonProvider<HttpPreprocessorAggregate>
 */
#[RequiresSingletonCollection(SingletonCollection::HttpPreprocessor)]
#[Singleton(provides: HttpPreprocessorAggregate::class)]
final readonly class HttpPreprocessorAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): HttpPreprocessorAggregate
    {
        $httpPreprocessorAggregate = new HttpPreprocessorAggregate();

        foreach ($this->collectPreprocessors($singletons) as $preprocessorAttribute) {
            $attributeClassName = $preprocessorAttribute->attribute->attribute;

            foreach ($phpProjectFiles->findByAttribute($attributeClassName) as $subjectAttribute) {
                $responderClassName = $subjectAttribute->reflectionClass->getName();

                if (!is_a($responderClassName, HttpResponderInterface::class, true)) {
                    throw new LogicException(sprintf(
                        '%s is not a HttpResponderInterface',
                        $subjectAttribute->reflectionClass->getName(),
                    ));
                }

                $httpPreprocessorAggregate->registerPreprocessor(
                    $preprocessorAttribute->singleton,
                    $responderClassName,
                    $subjectAttribute->attribute,
                    $preprocessorAttribute->attribute->priority,
                );
            }
        }

        $httpPreprocessorAggregate->sortPreprocessors();

        return $httpPreprocessorAggregate;
    }

    /**
     * @return iterable<SingletonAttribute<HttpPreprocessorInterface,PreprocessesHttpResponder>>
     */
    private function collectPreprocessors(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            HttpPreprocessorInterface::class,
            PreprocessesHttpResponder::class,
        );
    }
}
