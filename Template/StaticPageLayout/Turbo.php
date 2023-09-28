<?php

declare(strict_types=1);

namespace Resonance\Template\StaticPageLayout;

use Ds\Map;
use Generator;
use Resonance\StaticPage;
use Resonance\StaticPageCollectionAggregate;
use Resonance\StaticPageParentIterator;
use Resonance\Template\StaticPageLayout;
use Resonance\TemplateFilters;

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
        protected Map $staticPages,
        private StaticPageCollectionAggregate $staticPageCollectionAggregate,
        private TemplateFilters $filters,
    ) {}

    /**
     * @return Generator<string>
     */
    public function renderStaticPage(StaticPage $staticPage): Generator
    {
        yield <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="description" content="{$this->filters->escape($staticPage->frontMatter->description)}">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>{$staticPage->frontMatter->title}</title>
        HTML;
        yield <<<HTML
            <link rel="preload" href="{$this->versionedAsset('atkinson-hyperlegible-regular', 'ttf')}" as="font" type="font/ttf" crossorigin>
            <link rel="preload" href="{$this->versionedAsset('lora', 'ttf')}" as="font" crossorigin>
            <link rel="preload" href="{$this->versionedAsset('undefined-medium', 'ttf')}" as="font" crossorigin>
            <link rel="stylesheet" href="{$this->versionedAsset('docs', 'css')}">
            <script defer type="module" src="{$this->versionedAsset('global_turbo', 'js')}"></script>
            <script defer type="module" src="{$this->versionedAsset('global_stimulus', 'js')}"></script>
        HTML;
        yield from $this->renderMeta($staticPage);
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
     * @return Generator<string>
     */
    protected function renderMeta(StaticPage $staticPage): Generator
    {
        yield '';
    }

    protected function versionedAsset(string $basename, string $extension): string
    {
        return '/assets/'.$basename.'_'.DM_BUILD_ID.'.'.$extension;
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
}
