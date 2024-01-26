<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class FrontMatterCollectionReference
{
    /**
     * @param non-empty-string      $name
     * @param null|non-empty-string $next
     */
    public function __construct(
        public string $name,
        public ?string $next,
    ) {}
}
