<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @psalm-suppress PossiblyUnusedProperty used in templates
 */
readonly class CommonMarkTableOfContentsLink
{
    public function __construct(
        public int $level,
        public string $slug,
        public string $text,
    ) {}
}
