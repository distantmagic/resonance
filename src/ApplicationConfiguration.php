<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class ApplicationConfiguration
{
    /**
     * @param non-empty-string $esbuildMetafile
     * @param non-empty-string $scheme
     * @param non-empty-string $url
     */
    public function __construct(
        public Environment $environment,
        public string $esbuildMetafile,
        public string $scheme,
        public string $url,
    ) {}
}
