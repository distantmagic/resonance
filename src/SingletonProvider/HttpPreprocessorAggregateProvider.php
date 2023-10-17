<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute;
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

/**
 * @template-extends SingletonProvider<HttpPreprocessorAggregate>
 */
#[RequiresSingletonCollection(SingletonCollection::HttpPreprocessor)]
#[RequiresSingletonCollection(SingletonCollection::HttpResponder)]
#[Singleton(provides: HttpPreprocessorAggregate::class)]
final readonly class HttpPreprocessorAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): HttpPreprocessorAggregate
    {
        $httpPreprocessorAggregate = new HttpPreprocessorAggregate();

        foreach ($this->collectPreprocessors($singletons) as $preprocessorAttribute) {
            $attributeClassName = $preprocessorAttribute->attribute->attribute;

            foreach ($this->collectResponders($singletons, $attributeClassName) as $responderAttribute) {
                $httpPreprocessorAggregate->registerPreprocessor(
                    $preprocessorAttribute->singleton,
                    $responderAttribute->singleton,
                    $responderAttribute->attribute,
                );
            }
        }

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

    /**
     * @template TAttribute of Attribute
     *
     * @param class-string<TAttribute> $attribute
     *
     * @return iterable<SingletonAttribute<HttpResponderInterface,TAttribute>>
     */
    private function collectResponders(SingletonContainer $singletons, string $attribute): iterable
    {
        return $this->collectAttributes(
            $singletons,
            HttpResponderInterface::class,
            $attribute,
        );
    }
}
