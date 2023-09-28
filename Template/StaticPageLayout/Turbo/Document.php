<?php

declare(strict_types=1);

namespace Resonance\Template\StaticPageLayout\Turbo;

use Ds\Map;
use Ds\PriorityQueue;
use Generator;
use Resonance\CommonMarkRenderedContentWithTableOfContentsLinks;
use Resonance\EsbuildMeta;
use Resonance\StaticPage;
use Resonance\StaticPageCollectionAggregate;
use Resonance\StaticPageContentRenderer;
use Resonance\Template\Component\StaticPageBreadcrumbs;
use Resonance\Template\Component\StaticPageDocumentsMenu;
use Resonance\Template\Component\StaticPageDocumentTableOfContents;
use Resonance\Template\StaticPageLayout\Turbo;
use Resonance\TemplateFilters;

readonly class Document extends Turbo
{
    private StaticPageBreadcrumbs $breadcrumbs;
    private StaticPageDocumentsMenu $documentsMenu;
    private StaticPageDocumentTableOfContents $tableOfContents;

    /**
     * @param Map<string, StaticPage>    $staticPages
     * @param Map<StaticPage,StaticPage> $staticPagesFollowers
     * @param Map<StaticPage,StaticPage> $staticPagesPredecessors
     */
    public function __construct(
        EsbuildMeta $esbuildMeta,
        Map $staticPages,
        private Map $staticPagesFollowers,
        private Map $staticPagesPredecessors,
        StaticPageCollectionAggregate $staticPageCollectionAggregate,
        private StaticPageContentRenderer $staticPageContentRenderer,
        TemplateFilters $filters,
    ) {
        parent::__construct(
            $esbuildMeta,
            $staticPages,
            $staticPageCollectionAggregate,
            $filters,
        );

        $this->breadcrumbs = new StaticPageBreadcrumbs($staticPages);
        $this->documentsMenu = new StaticPageDocumentsMenu(
            $staticPages,
            $staticPageCollectionAggregate,
            1,
        );
        $this->tableOfContents = new StaticPageDocumentTableOfContents();
    }

    /**
     * @param PriorityQueue<string> $scripts
     */
    protected function registerScripts(PriorityQueue $scripts): void
    {
        parent::registerScripts($scripts);

        $scripts->push('controller_hljs.ts', 0);
    }

    protected function renderBodyContent(StaticPage $staticPage): Generator
    {
        $renderedContent = $this->staticPageContentRenderer
            ->markdownParser
            ->converter
            ->convert($staticPage->content)
        ;

        $documentationClass = '';

        if ($renderedContent instanceof CommonMarkRenderedContentWithTableOfContentsLinks) {
            $documentationClass = 'documentation--with-toc';
        }

        yield <<<HTML
        <div class="documentation {$documentationClass}">
            <nav class="documentation__aside">
                <div class="documentation__aside__links">
        HTML;
        yield from $this->documentsMenu->render($staticPage);
        yield <<<HTML
                </div>
            </nav>
            <article class="documentation__article">
                {$renderedContent->getContent()}
        HTML;
        yield from $this->renderNextPageReference($staticPage);
        yield <<<'HTML'
            </article>
            <nav class="documentation__breadcrumbs">
        HTML;
        yield from $this->breadcrumbs->render($staticPage);
        yield '</nav>';
        yield from $this->tableOfContents->render($renderedContent);
        yield '</div>';
    }

    protected function renderMeta(StaticPage $staticPage): Generator
    {
        $nextPage = $this->staticPagesFollowers->get($staticPage, null);

        if (isset($nextPage)) {
            yield <<<HTML
            <link rel="next" href="{$nextPage->getHref()}">
            HTML;
        }

        $prevPage = $this->staticPagesPredecessors->get($staticPage, null);

        if (isset($prevPage)) {
            yield <<<HTML
            <link rel="prev" href="{$prevPage->getHref()}">
            HTML;
        }
    }

    /**
     * @return Generator<string>
     */
    protected function renderNextPageReference(StaticPage $staticPage): Generator
    {
        $nextPage = $this->staticPagesFollowers->get($staticPage, null);
        $prevPage = $this->staticPagesPredecessors->get($staticPage, null);

        if (!isset($nextPage) && !isset($prevPage)) {
            return;
        }

        yield <<<'HTML'
        <div class="documentation__article__footer-navigation">
        HTML;
        if (isset($prevPage)) {
            yield <<<HTML
            <a
                class="
                    documentation__article__footer-navigation__link
                    documentation__article__footer-navigation__link--prev
                "
                href="{$prevPage->getHref()}"
            >
                <div class="documentation__article__footer-navigation__label">
                    Previous
                </div>
                <div class="documentation__article__footer-navigation__title">
                    &laquo; {$prevPage->frontMatter->title}
                </div>
            </a>
            HTML;
        }
        if (isset($nextPage)) {
            yield <<<HTML
            <a
                class="
                    documentation__article__footer-navigation__link
                    documentation__article__footer-navigation__link--next
                "
                href="{$nextPage->getHref()}"
            >
                <div class="documentation__article__footer-navigation__label">
                    Next
                </div>
                <div class="documentation__article__footer-navigation__title">
                    {$nextPage->frontMatter->title} &raquo;
                </div>
            </a>
            HTML;
        }
        yield <<<'HTML'
        </div>
        HTML;
    }
}
