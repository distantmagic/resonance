<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use SensitiveParameter;

readonly class StaticPageConfiguration
{
    /**
     * @psalm-taint-source file $esbuildMetafile
     * @psalm-taint-source file $outputDirectory
     * @psalm-taint-source file $sitemap
     */
    public function __construct(
        public string $baseUrl,
        #[SensitiveParameter]
        public string $esbuildMetafile,
        #[SensitiveParameter]
        public string $inputDirectory,
        public string $outputDirectory,
        #[SensitiveParameter]
        public string $sitemap,
        public string $stripOutputPrefix,
    ) {}
}
