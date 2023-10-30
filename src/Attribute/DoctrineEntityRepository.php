<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class DoctrineEntityRepository extends BaseAttribute
{
    /**
     * @param class-string $entityClass
     */
    public function __construct(
        public string $entityClass,
        public string $connection = 'default',
    ) {}
}
