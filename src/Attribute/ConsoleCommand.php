<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class ConsoleCommand extends BaseAttribute
{
    public function __construct(
        public string $name,
        public ?string $description = null,
    ) {}
}
