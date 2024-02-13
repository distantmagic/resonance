<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
final readonly class RequiresPhpExtension extends BaseAttribute
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(public string $name) {}
}
