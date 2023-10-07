<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Template\StaticPageLayout;

use Distantmagic\Resonance\EsbuildMeta;
use Distantmagic\Resonance\EsbuildMetaEntryPoints;
use Distantmagic\Resonance\EsbuildMetaPreloadsRenderer;
use Distantmagic\Resonance\StaticPage;
use Distantmagic\Resonance\StaticPageCollectionAggregate;
use Distantmagic\Resonance\StaticPageParentIterator;
use Distantmagic\Resonance\Template\StaticPageLayout;
use Distantmagic\Resonance\TemplateFilters;
use Ds\Map;
use Ds\PriorityQueue;
use Generator;

abstract readonly class Turbo extends StaticPageLayout
{
    /**
     * @return Generator<string>
     */
    abstract protected function renderBodyContent(StaticPage $staticPage): Generator;

    /**
     * @param Map<string, StaticPage> $staticPages
     */
    public function __construct(
        private EsbuildMeta $esbuildMeta,
        protected Map $staticPages,
        private StaticPageCollectionAggregate $staticPageCollectionAggregate,
        private TemplateFilters $filters,
    ) {}

    /**
     * @return Generator<string>
     */
    public function renderStaticPage(StaticPage $staticPage): Generator
    {
        $esbuildMetaEntryPoints = new EsbuildMetaEntryPoints($this->esbuildMeta);
        $esbuildPreloadsRenderer = new EsbuildMetaPreloadsRenderer($esbuildMetaEntryPoints, $this->filters);

        $renderedScripts = $this->renderScripts($esbuildMetaEntryPoints);
        $renderedStylesheets = $this->renderStylesheets($staticPage, $esbuildMetaEntryPoints);
        $renderedPreloads = $esbuildPreloadsRenderer->render();

        yield <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
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
        yield '</nav>';
        yield from $this->renderBodyContent($staticPage);
        yield <<<'HTML'
                <footer class="primary-footer">
                    <div class="primary-footer__link-groups">
                        <nav class="primary-footer__links">
                            <div class="primary-footer__links__header">
                                Community
                            </div>
                            <a href="https://discord.gg/kysUzFqSCK" rel="external ugc">
                                Discord
                            </a>
                        </nav>
                    </div>
                    <div class="primary-footer__copyright">
                        Copyright &copy; 2023 Distantmagic.
                    </div>
                </footer>
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
    protected function registerStylesheets(PriorityQueue $stylesheets): void {}

    /**
     * @return Generator<string>
     */
    protected function renderMeta(StaticPage $staticPage): Generator
    {
        yield '';
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
