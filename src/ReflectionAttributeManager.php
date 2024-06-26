<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Set;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionFunction;
use RuntimeException;

readonly class ReflectionAttributeManager
{
    public function __construct(private ReflectionClass|ReflectionFunction $reflection) {}

    /**
     * @template TAttribute of Attribute
     *
     * @param class-string<TAttribute> $attributeClassName
     *
     * @return null|TAttribute
     */
    public function findAttribute(string $attributeClassName): ?object
    {
        $ret = null;

        foreach ($this->findAttributes($attributeClassName) as $attribute) {
            if (isset($ret)) {
                throw new RuntimeException(sprintf('Attribute is repeated: "%s"', $attributeClassName));
            }

            $ret = $attribute;
        }

        return $ret;
    }

    /**
     * @template TAttribute of Attribute
     *
     * @param class-string<TAttribute> $attributeClassName
     *
     * @return Set<TAttribute>
     */
    public function findAttributes(string $attributeClassName): Set
    {
        $reflectionAttributes = $this
            ->reflection
            ->getAttributes($attributeClassName, ReflectionAttribute::IS_INSTANCEOF)
        ;

        /**
         * @var Set<TAttribute> $ret
         */
        $ret = new Set();

        foreach ($reflectionAttributes as $reflectionAttribute) {
            $ret->add($reflectionAttribute->newInstance());
        }

        return $ret;
    }
}
