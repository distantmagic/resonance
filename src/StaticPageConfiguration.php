<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class StaticPageConfiguration
{
    public function __construct(
        public string $baseUrl,
        public string $esbuildMetafile,
        public string $inputDirectory,
        public string $outputDirectory,
        public string $sitemap,
    ) {}
}
