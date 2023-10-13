<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class ConsoleCommand extends BaseAttribute
{
    /**
     * @param array<string> $aliases
     */
    public function __construct(
        public string $name,
        public array $aliases = [],
        public ?string $description = null,
        public bool $isEnabled = true,
        public bool $isHidden = false,
    ) {}
}
