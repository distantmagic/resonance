<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;
use Distantmagic\Resonance\GraphQLRootFieldType;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class GraphQLRootField extends BaseAttribute
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(
        public string $name,
        public GraphQLRootFieldType $type,
    ) {}
}
