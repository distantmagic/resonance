<?php

declare(strict_types=1);

namespace Resonance;

use Generator;
use IteratorAggregate;
use LogicException;

/**
 * @template TSingleton of object
 * @template TAttribute of Attribute
 *
 * @template-implements IteratorAggregate<SingletonAttribute<TSingleton,TAttribute>>
 */
readonly class SingletonAttributeClassFilterInterator implements IteratorAggregate
{
    /**
     * @param SingletonContainerAttributeIterator<TAttribute> $singletonContainerAttributeIterator
     * @param class-string<TSingleton>                        $className
     */
    public function __construct(
        private SingletonContainerAttributeIterator $singletonContainerAttributeIterator,
        private string $className,
    ) {}

    /**
     * @return Generator<SingletonAttribute<TSingleton,TAttribute>>
     */
    public function getIterator(): Generator
    {
        foreach ($this->singletonContainerAttributeIterator as $singletonAttribute) {
            if (!($singletonAttribute->singleton instanceof $this->className)) {
                throw new LogicException(sprintf(
                    'Unexpected collection element. Expected %s got %s',
                    $this->className,
                    $singletonAttribute->singleton::class,
                ));
            }

            yield new SingletonAttribute(
                $singletonAttribute->singleton,
                $singletonAttribute->attribute,
            );
        }
    }
}
