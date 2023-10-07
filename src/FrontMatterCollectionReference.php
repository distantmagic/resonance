<?php

declare(strict_types=1);

namespace Resonance;

readonly class FrontMatterCollectionReference
{
    public function __construct(
        public string $name,
        public ?string $next,
    ) {}
}
