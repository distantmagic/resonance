<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\InputValidator\FrontMatterValidator;
use Ds\Map;
use Ds\Set;
use Nette\Schema\Processor;
use RuntimeException;
use Swoole\Coroutine\WaitGroup;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

use function Swoole\Coroutine\go;

#[Singleton]
readonly class StaticPageProcessor
{
    public function __construct(
        private ConsoleOutput $output,
        private Processor $processor,
        private StaticPageConfiguration $staticPageConfiguration,
    ) {}

    /**
     * Produces a list of errors. If it's empty then all is fine.
     *
     * @return Map<SplFileInfo, Set<string>>
     */
    public function process(
        string $esbuildMetafile,
        string $staticPagesInputDirectory,
        string $staticPagesOutputDirectory,
        string $staticPagesSitemap,
        string $stripOutputPrefix = '',
    ): Map {
        /**
         * @var Map<SplFileInfo,Set<string>> $errors
         */
        $errors = new Map();

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
        $staticPageContentRenderer = new StaticPageContentRenderer(
            $staticPages,
            $this->staticPageConfiguration,
        );
        $staticPageIterator = new StaticPageIterator(
            new FrontMatterValidator($this->processor),
            $fileIterator,
            $staticPagesOutputDirectory,
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
            $this->reportError($errors, $exception->splFileInfo, $exception->getMessage());

            return $errors;
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
                $this->reportError($errors, $staticPage->file, $exception->getMessage());

                return $errors;
            }
        }

        $staticPageCollectionAggregate->sortCollections($staticPages);

        // Third pass - render pages using layouts and the metadata collected in the
        // first pass.
        // Wrapped in coroutines because it can generate a lot of IO operations.

        $staticPagesCount = $staticPages->count();
        $waitGroup = new WaitGroup($staticPagesCount);

        foreach ($staticPages as $staticPage) {
            /**
             * @var false|int $cid
             */
            $cid = go(function () use ($errors, $filesystem, $staticPage, $staticPageLayoutAggregate, $waitGroup) {
                $outputDirectory = $staticPage->getOutputDirectory();
                $outputFilename = $staticPage->getOutputPathname();

                $filesystem->mkdir($outputDirectory);

                $fhandle = fopen($outputFilename, 'w');

                try {
                    foreach ($staticPageLayoutAggregate->render($staticPage) as $contentChunk) {
                        fwrite($fhandle, $contentChunk);
                    }
                } catch (StaticPageReferenceException $exception) {
                    $this->reportError($errors, $staticPage->file, $exception->getMessage());
                } catch (StaticPageRenderingException $exception) {
                    $this->reportError(
                        $errors,
                        $staticPage->file,
                        $exception->getMessage().': '.(string) $exception->getPrevious()?->getMessage()
                    );
                } finally {
                    $waitGroup->done();
                    fclose($fhandle);
                }
            });

            if (!is_int($cid)) {
                $this->reportError($errors, $staticPage->file, 'Unable to start a session write coroutine.');
            }
        }

        // Wait 100 miliseconds per page
        if (!$waitGroup->wait($staticPagesCount * 0.1)) {
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

            return $errors;
        }

        // Fourth pass - generate a sitemap
        $sitemapGenerator = new StaticPageSitemapGenerator(
            $staticPages,
            $this->staticPageConfiguration,
        );
        $sitemapGenerator->writeTo($staticPagesSitemap);

        return $errors;
    }

    /**
     * @param Map<SplFileInfo,Set<string>> $errors
     */
    private function reportError(
        Map $errors,
        SplFileInfo $file,
        string $message,
    ): void {
        if (!$errors->hasKey($file)) {
            $errors->put($file, new Set());
        }

        $errors->get($file)->add($message);
    }
}
