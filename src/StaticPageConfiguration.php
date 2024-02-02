<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use SensitiveParameter;

/**
 * @psalm-suppress PossiblyUnusedProperty used in applications
 */
readonly class StaticPageConfiguration
{
    /**
     * @psalm-taint-source file $esbuildMetafile
     * @psalm-taint-source file $outputDirectory
     * @psalm-taint-source file $sitemap
     *
     * @param non-empty-string $baseUrl
     * @param non-empty-string $esbuildMetafile
     * @param non-empty-string $inputDirectory
     * @param non-empty-string $outputDirectory
     * @param non-empty-string $sitemap
     * @param non-empty-string $stripOutputPrefix
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
