<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class ApplicationConfiguration
{
    public function __construct(
        public Environment $environment,
        public string $esbuildMetafile,
    ) {}
}
