<?php

declare(strict_types=1);

namespace Distantmagic\Docs\Template\StaticPageLayout;

use Distantmagic\Docs\Template\StaticPageLayout;
use Distantmagic\Resonance\EsbuildMeta;
use Distantmagic\Resonance\EsbuildMetaBuilder;
use Distantmagic\Resonance\EsbuildMetaEntryPoints;
use Distantmagic\Resonance\EsbuildMetaPreloadsRenderer;
use Distantmagic\Resonance\StaticPage;
use Distantmagic\Resonance\StaticPageCollectionAggregate;
use Distantmagic\Resonance\StaticPageConfiguration;
use Distantmagic\Resonance\StaticPageParentIterator;
use Distantmagic\Resonance\TemplateFilters;
use Ds\Map;
use Ds\PriorityQueue;
use Generator;

abstract readonly class Turbo extends StaticPageLayout
{
    private EsbuildMeta $esbuildMeta;

    /**
     * @return Generator<string>
     */
    abstract protected function renderBodyContent(StaticPage $staticPage): Generator;

    /**
     * @param Map<string, StaticPage> $staticPages
     */
    public function __construct(
        private EsbuildMetaBuilder $esbuildMetaBuilder,
        protected Map $staticPages,
        private StaticPageCollectionAggregate $staticPageCollectionAggregate,
        private StaticPageConfiguration $staticPageConfiguration,
        private TemplateFilters $filters,
    ) {
        $this->esbuildMeta = $this->esbuildMetaBuilder->build(
            $this->staticPageConfiguration->esbuildMetafile,
            $this->staticPageConfiguration->stripOutputPrefix,
        );
    }

    /**
     * @return Generator<string>
     */
    public function renderStaticPage(StaticPage $staticPage): Generator
    {
        $esbuildMetaEntryPoints = new EsbuildMetaEntryPoints($this->esbuildMeta);
        $esbuildPreloadsRenderer = new EsbuildMetaPreloadsRenderer($esbuildMetaEntryPoints);

        $renderedScripts = $this->renderScripts($esbuildMetaEntryPoints);
        $renderedStylesheets = $this->renderStylesheets($staticPage, $esbuildMetaEntryPoints);
        $renderedPreloads = $esbuildPreloadsRenderer->render();

        $canonicalUrl = $this->staticPageConfiguration->baseUrl.$staticPage->getHref();
        $currentYear = date('Y');

        yield <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <link rel="canonical" href="{$canonicalUrl}">
            <meta charset="utf-8">
            <meta name="description" content="{$this->filters->escape($staticPage->frontMatter->description)}">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>{$staticPage->frontMatter->title}</title>
        HTML;
        yield from $this->renderMeta($staticPage);
        yield $renderedPreloads;
        yield $renderedStylesheets;
        yield $renderedScripts;
        yield <<<'HTML'
        </head>
        <body>
            <main class="body-content website">
                <nav class="primary-navigation">
        HTML;
        yield from $this->renderPrimaryNavigation($staticPage);
        yield <<<'HTML'
        <a
            class="primary-navigation__github-link"
            href="https://github.com/distantmagic/resonance"
            target="_blank"
        >
            <svg viewbox="0 0 98 96" xmlns="http://www.w3.org/2000/svg">
                <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M48.854 0C21.839 0 0 22 0 49.217c0 21.756 13.993 40.172 33.405 46.69 2.427.49 3.316-1.059 3.316-2.362 0-1.141-.08-5.052-.08-9.127-13.59 2.934-16.42-5.867-16.42-5.867-2.184-5.704-5.42-7.17-5.42-7.17-4.448-3.015.324-3.015.324-3.015 4.934.326 7.523 5.052 7.523 5.052 4.367 7.496 11.404 5.378 14.235 4.074.404-3.178 1.699-5.378 3.074-6.6-10.839-1.141-22.243-5.378-22.243-24.283 0-5.378 1.94-9.778 5.014-13.2-.485-1.222-2.184-6.275.486-13.038 0 0 4.125-1.304 13.426 5.052a46.97 46.97 0 0 1 12.214-1.63c4.125 0 8.33.571 12.213 1.63 9.302-6.356 13.427-5.052 13.427-5.052 2.67 6.763.97 11.816.485 13.038 3.155 3.422 5.015 7.822 5.015 13.2 0 18.905-11.404 23.06-22.324 24.283 1.78 1.548 3.316 4.481 3.316 9.126 0 6.6-.08 11.897-.08 13.526 0 1.304.89 2.853 3.316 2.364 19.412-6.52 33.405-24.935 33.405-46.691C97.707 22 75.788 0 48.854 0z"
                    fill="#ddd"
                />
            </svg>
        </a>
        HTML;
        yield '</nav>';
        yield from $this->renderBodyContent($staticPage);
        yield <<<HTML
                <footer class="primary-footer">
                    <div class="primary-footer__copyright">
                        Copyright &copy; {$currentYear} Distantmagic.
                        Built with Resonance.
                    </div>
                </footer>
                <a
                    class="global-edit-on-github"
                    href="https://github.com/distantmagic/resonance/tree/master/docs/pages/{$staticPage->file->getRelativePathname()}"
                    rel="noopener noreferrer"
                    target="_blank"
                >
                    Edit on GitHub
                </a>
            </main>
        </body>
        </html>
        HTML;
    }

    /**
     * @param PriorityQueue<string> $scripts
     */
    protected function registerScripts(PriorityQueue $scripts): void
    {
        $scripts->push('global_turbo.ts', 900);
        $scripts->push('global_stimulus.ts', 800);
    }

    /**
     * @param PriorityQueue<string> $stylesheets
     */
    protected function registerStylesheets(PriorityQueue $stylesheets): void
    {
        $stylesheets->push('docs-common.css', 1000);
    }

    /**
     * @return Generator<string>
     */
    protected function renderMeta(StaticPage $staticPage): Generator
    {
        $nextPage = $this->staticPageCollectionAggregate->pagesFollowers->get($staticPage, null);

        if (isset($nextPage)) {
            yield <<<HTML
            <link rel="next" href="{$nextPage->getHref()}">
            HTML;
        }

        $prevPage = $this->staticPageCollectionAggregate->pagesPredecessors->get($staticPage, null);

        if (isset($prevPage)) {
            yield <<<HTML
            <link rel="prev" href="{$prevPage->getHref()}">
            HTML;
        }
    }

    private function isLinkActive(StaticPage $staticPage, StaticPage $currentPage): bool
    {
        foreach (new StaticPageParentIterator($this->staticPages, $currentPage) as $parentPage) {
            if ($staticPage->is($parentPage)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Generator<string>
     */
    private function renderPrimaryNavigation(StaticPage $currentPage): Generator
    {
        $staticPages = $this
            ->staticPageCollectionAggregate
            ->useCollection('primary_navigation')
            ->staticPages
        ;

        foreach ($staticPages as $staticPage) {
            yield sprintf(
                '<a class="%s" href="%s">%s</a>'."\n",
                $this->isLinkActive($staticPage, $currentPage) ? 'active' : '',
                $staticPage->getHref(),
                $staticPage->frontMatter->title,
            );
        }
    }

    private function renderScripts(EsbuildMetaEntryPoints $esbuildMetaEntryPoints): string
    {
        /**
         * @var PriorityQueue<string> $scripts
         */
        $scripts = new PriorityQueue();

        $this->registerScripts($scripts);

        $ret = '';

        foreach ($scripts as $script) {
            $ret .= sprintf(
                '<script defer type="module" src="%s"></script>'."\n",
                '/'.$esbuildMetaEntryPoints->resolveEntryPointPath($script),
            );
        }

        return $ret;
    }

    private function renderStylesheets(
        StaticPage $staticPage,
        EsbuildMetaEntryPoints $esbuildMetaEntryPoints,
    ): string {
        /**
         * @var PriorityQueue<string> $stylesheets
         */
        $stylesheets = new PriorityQueue();

        $this->registerStylesheets($stylesheets);

        foreach ($staticPage->frontMatter->registerStylesheets as $stylesheet) {
            $stylesheets->push($stylesheet, 0);
        }

        $ret = '';

        foreach ($stylesheets as $stylesheet) {
            $ret .= sprintf(
                '<link rel="stylesheet" href="%s">'."\n",
                '/'.$esbuildMetaEntryPoints->resolveEntryPointPath($stylesheet),
            );
        }

        return $ret;
    }
}
