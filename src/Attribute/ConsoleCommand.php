<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class ConsoleCommand extends BaseAttribute
{
    /**
     * @param non-empty-string      $name
     * @param array<string>         $aliases
     * @param null|non-empty-string $description
     */
    public function __construct(
        public string $name,
        public array $aliases = [],
        public ?string $description = null,
        public bool $isEnabled = true,
        public bool $isHidden = false,
    ) {}
}
