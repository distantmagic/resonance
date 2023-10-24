<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use RuntimeException;
use Swoole\Coroutine\WaitGroup;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

use function Swoole\Coroutine\go;

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

        // Render pages using layouts and the metadata..
        // Wrapped in coroutines because it can generate a lot of IO operations.

        $staticPagesCount = $this->staticPageAggregate->staticPages->count();
        $waitGroup = new WaitGroup($staticPagesCount);

        foreach ($this->staticPageAggregate->staticPages as $staticPage) {
            /**
             * @var false|int $cid
             */
            $cid = go(function () use ($filesystem, $staticPage, $waitGroup) {
                $outputDirectory = $staticPage->getOutputDirectory();
                $outputFilename = $staticPage->getOutputPathname();

                $filesystem->mkdir($outputDirectory);

                $fhandle = fopen($outputFilename, 'w');

                try {
                    foreach ($this->staticPageLayoutAggregate->render($staticPage) as $contentChunk) {
                        fwrite($fhandle, $contentChunk);
                    }
                } finally {
                    $waitGroup->done();
                    fclose($fhandle);
                }
            });

            if (!is_int($cid)) {
                throw new RuntimeException('Unable to start a session write coroutine.');
            }
        }

        // Wait 100 miliseconds per page
        if (!$waitGroup->wait($staticPagesCount * 0.1)) {
            throw new RuntimeException('Static pages wait group took too long to finish.');
        }

        // Generate a sitemap
        $this->sitemapGenerator->writeTo($this->staticPageConfiguration->sitemap);
    }
}
