<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TObject of object
 *
 * @template-implements SingletonProviderInterface<TObject>
 */
abstract readonly class SingletonProvider implements SingletonProviderInterface
{
    /**
     * @template TFilteredObject of object
     * @template TFilteredAttribute of Attribute
     *
     * @param class-string<TFilteredObject>    $className
     * @param class-string<TFilteredAttribute> $attributeName
     *
     * @return iterable<SingletonAttribute<TFilteredObject,TFilteredAttribute>>
     */
    protected function collectAttributes(
        SingletonContainer $singletons,
        string $className,
        string $attributeName,
    ): iterable {
        return new SingletonAttributeClassFilterInterator(
            new SingletonContainerAttributeIterator($singletons, $attributeName),
            $className,
        );
    }
}
