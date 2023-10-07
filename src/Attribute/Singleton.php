<?php

declare(strict_types=1);

namespace Resonance\Attribute;

use Attribute;
use Resonance\Attribute as BaseAttribute;
use Resonance\SingletonCollectionInterface;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class Singleton extends BaseAttribute
{
    /**
     * @param null|class-string $provides
     */
    public function __construct(
        public ?SingletonCollectionInterface $collection = null,
        public ?SingletonCollectionInterface $requiresCollection = null,
        public ?string $provides = null,
    ) {}
}
