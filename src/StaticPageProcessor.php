<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\Singleton;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

#[GrantsFeature(Feature::StaticPages)]
#[Singleton]
readonly class StaticPageProcessor
{
    public function __construct(
        private StaticPageAggregate $staticPageAggregate,
        private StaticPageConfiguration $staticPageConfiguration,
        private StaticPageLayoutAggregate $staticPageLayoutAggregate,
        private StaticPageSitemapGenerator $sitemapGenerator,
    ) {}

    public function process(): void
    {
        $removableFiles = Finder::create()
            ->exclude('assets')
            ->in($this->staticPageConfiguration->outputDirectory)
        ;

        $filesystem = new Filesystem();
        $filesystem->remove($removableFiles);

        foreach ($this->staticPageAggregate->staticPages as $staticPage) {
            /**
             * @var false|int $cid
             */
            $outputDirectory = $staticPage->getOutputDirectory();
            $outputFilename = $staticPage->getOutputPathname();

            $filesystem->mkdir($outputDirectory);

            $fhandle = fopen($outputFilename, 'w');

            try {
                foreach ($this->staticPageLayoutAggregate->render($staticPage) as $contentChunk) {
                    fwrite($fhandle, $contentChunk);
                }
            } finally {
                fclose($fhandle);
            }
        }

        // Generate a sitemap
        $this->sitemapGenerator->writeTo($this->staticPageConfiguration->sitemap);
    }
}
