<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Attribute;

use Attribute;
use Distantmagic\Resonance\Attribute as BaseAttribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class RespondsToPromptSubject extends BaseAttribute
{
    /**
     * @param non-empty-string        $action
     * @param non-empty-string        $subject
     * @param array<non-empty-string> $examples
     */
    public function __construct(
        public string $action,
        public string $subject,
        public array $examples = [],
    ) {}
}
