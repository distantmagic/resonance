<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;
use Distantmagic\Resonance\OpenAPISchemaSymbol;
use Distantmagic\Resonance\OpenAPISchemaSymbolInterface;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
final readonly class BelongsToOpenAPISchema extends BaseAttribute
{
    public function __construct(
        public OpenAPISchemaSymbolInterface $schemaSymbol = OpenAPISchemaSymbol::All,
    ) {}
}
