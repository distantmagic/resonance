<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;

readonly class StaticPageConfiguration
{
    /**
     * @param Map<string,string> $globals
     */
    public function __construct(
        public Map $globals,
        public string $baseUrl,
        public string $esbuildMetafile,
        public string $inputDirectory,
        public string $outputDirectory,
        public string $sitemap,
        public string $stripOutputPrefix,
    ) {}
}
