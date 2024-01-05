<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;
use Distantmagic\Resonance\OpenAPICollectionSymbolInterface;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
final readonly class BelongsToOpenAPICollection extends BaseAttribute
{
    public function __construct(
        public OpenAPICollectionSymbolInterface $collectionSymbol,
    ) {}
}
