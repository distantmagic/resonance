<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use Distantmagic\Resonance\InputValidator\FrontMatterValidator;
use RuntimeException;
use Swoole\Coroutine\WaitGroup;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

use function Swoole\Coroutine\go;

readonly class StaticPageProcessor
{
    public function __construct(
        private Output $output = new ConsoleOutput(),
    ) {}

    public function process(
        string $esbuildMetafile,
        string $staticPagesInputDirectory,
        string $staticPagesOutputDirectory,
        string $staticPagesSitemap,
        string $stripOutputPrefix = '',
    ): void {
        $esbuildMetaBuilder = new EsbuildMetaBuilder();
        $esbuildMeta = $esbuildMetaBuilder->build($esbuildMetafile, $stripOutputPrefix);

        /**
         * @var Map<string, StaticPage>
         */
        $staticPages = new Map();

        /**
         * @var Map<StaticPage, StaticPage>
         */
        $staticPagesFollowers = new Map();

        /**
         * @var Map<StaticPage, StaticPage>
         */
        $staticPagesPredecessors = new Map();

        $fileIterator = new StaticPageFileIterator($staticPagesInputDirectory);
        $staticPageCollectionAggregate = new StaticPageCollectionAggregate();
        $staticPageContentRenderer = new StaticPageContentRenderer($staticPages);
        $staticPageIterator = new StaticPageIterator(
            new FrontMatterValidator(),
            $fileIterator,
        );
        $staticPageLayoutAggregate = new StaticPageLayoutAggregate(
            $esbuildMeta,
            $staticPages,
            $staticPagesFollowers,
            $staticPagesPredecessors,
            $staticPageCollectionAggregate,
            $staticPageContentRenderer,
        );

        $removableFiles = Finder::create()
            ->exclude('assets')
            ->in($staticPagesOutputDirectory)
        ;

        $filesystem = new Filesystem();
        $filesystem->remove($removableFiles);

        // First pass - parse the FrontMatter, add pages to collections for later use.
        try {
            foreach ($staticPageIterator as $staticPage) {
                $staticPages->put($staticPage->getBasename(), $staticPage);
                $staticPageCollectionAggregate->addToCollections($staticPage);
            }
        } catch (StaticPageFileException $exception) {
            $this->reportError($exception->splFileInfo, $exception->getMessage());
        }

        // Second pass - organize the collections
        foreach ($staticPages as $staticPage) {
            try {
                $nextBasename = $staticPage->frontMatter->next;

                if (!isset($nextBasename)) {
                    continue;
                }

                if (!$staticPages->hasKey($nextBasename)) {
                    throw new StaticPageReferenceException('Static Page referenced in the "next" field does not exist: '.$nextBasename);
                }

                $nextStaticPage = $staticPages->get($nextBasename);

                $staticPagesFollowers->put($staticPage, $nextStaticPage);
                $staticPagesPredecessors->put($nextStaticPage, $staticPage);
            } catch (StaticPageReferenceException $exception) {
                $this->reportError($staticPage->file, $exception->getMessage());
            }
        }

        $staticPageCollectionAggregate->sortCollections($staticPages);

        // Third pass - render pages using layouts and the metadata collected in the
        // first pass.
        // Wrapped in coroutines because it can generate a lot of IO operations.

        $waitGroup = new WaitGroup();

        foreach ($staticPages as $staticPage) {
            $waitGroup->add();
            $cid = go(function () use ($filesystem, $staticPage, $staticPageLayoutAggregate, $waitGroup) {
                $outputDirectory = $staticPage->getOutputDirectory();
                $outputFilename = $staticPage->getOutputPathname();

                $filesystem->mkdir($outputDirectory);

                $fhandle = fopen($outputFilename, 'w');

                try {
                    foreach ($staticPageLayoutAggregate->render($staticPage) as $contentChunk) {
                        fwrite($fhandle, $contentChunk);
                    }
                } catch (StaticPageReferenceException $exception) {
                    $this->reportError($staticPage->file, $exception->getMessage());
                } catch (StaticPageRenderingException $exception) {
                    $this->reportError(
                        $staticPage->file,
                        $exception->getMessage().': '.(string) $exception->getPrevious()?->getMessage()
                    );
                } finally {
                    $waitGroup->done();
                    fclose($fhandle);
                }
            });

            if (!is_int($cid)) {
                $this->reportError($staticPage->file, 'Unable to start a session write coroutine.');
            }
        }

        // Wait 100 miliseconds per page
        if (!$waitGroup->wait($staticPages->count() * 0.1)) {
            throw new RuntimeException('Static pages wait group took too long to finish.');
        }

        // Unused collections check for data consistency.
        if (!$staticPageCollectionAggregate->unusedCollections->isEmpty()) {
            $this->output->write('Documents are assigned to collections that are never used. ');
            $this->output->writeln('Please remove those collections from pages or reference them in either layout or a static page:');

            foreach ($staticPageCollectionAggregate->unusedCollections as $collectionName) {
                $collection = $staticPageCollectionAggregate->useCollection($collectionName);

                foreach ($collection->staticPages as $staticPage) {
                    $this->output->writeln(sprintf(
                        '%s -> %s',
                        $staticPage->file->getRelativePathname(),
                        $collectionName,
                    ));
                }
            }

            exit(1);
        }

        // Fourth pass - generate a sitemap
        $sitemapGenerator = new StaticPageSitemapGenerator($staticPages);
        $sitemapGenerator->writeTo($staticPagesSitemap);
    }

    private function reportError(SplFileInfo $file, string $message): never
    {
        $this->output->writeln(sprintf(
            '%s: %s',
            'Error while processing '.$file->getRelativePathname(),
            $message,
        ));

        exit(1);
    }
}
