<?php

declare(strict_types=1);

namespace Distantmagic\Docs\Template\StaticPageLayout\Turbo;

use Distantmagic\Docs\Template\Component\StaticPageBreadcrumbs;
use Distantmagic\Docs\Template\Component\StaticPageDocumentsMenu;
use Distantmagic\Docs\Template\Component\StaticPageDocumentTableOfContents;
use Distantmagic\Docs\Template\StaticPageLayout\Turbo;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\StaticPageLayout;
use Distantmagic\Resonance\CommonMarkTableOfContentsBuilder;
use Distantmagic\Resonance\EsbuildMetaBuilder;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\StaticPage;
use Distantmagic\Resonance\StaticPageAggregate;
use Distantmagic\Resonance\StaticPageCollectionAggregate;
use Distantmagic\Resonance\StaticPageConfiguration;
use Distantmagic\Resonance\StaticPageMarkdownParser;
use Distantmagic\Resonance\TemplateFilters;
use Ds\PriorityQueue;
use Generator;
use IntlDateFormatter;

#[Singleton(collection: SingletonCollection::StaticPageLayout)]
#[StaticPageLayout('dm:document')]
final readonly class Document extends Turbo
{
    private StaticPageBreadcrumbs $breadcrumbs;
    private StaticPageDocumentsMenu $documentsMenu;
    private IntlDateFormatter $intlDateFormatter;
    private StaticPageDocumentTableOfContents $tableOfContents;
    private CommonMarkTableOfContentsBuilder $tableOfContentsBuilder;

    public function __construct(
        EsbuildMetaBuilder $esbuildMetaBuilder,
        private StaticPageCollectionAggregate $staticPageCollectionAggregate,
        StaticPageConfiguration $staticPageConfiguration,
        private StaticPageMarkdownParser $staticPageMarkdownParser,
        StaticPageAggregate $staticPageAggregate,
        TemplateFilters $filters,
    ) {
        parent::__construct(
            $esbuildMetaBuilder,
            $staticPageAggregate->staticPages,
            $staticPageCollectionAggregate,
            $staticPageConfiguration,
            $filters,
        );

        $this->breadcrumbs = new StaticPageBreadcrumbs($staticPageAggregate->staticPages);
        $this->documentsMenu = new StaticPageDocumentsMenu(
            $staticPageAggregate->staticPages,
            $staticPageCollectionAggregate,
            1,
        );
        $this->intlDateFormatter = new IntlDateFormatter(
            'en',
            IntlDateFormatter::LONG,
            IntlDateFormatter::NONE,
        );
        $this->tableOfContents = new StaticPageDocumentTableOfContents(
            'documentation',
            '.documentation__article',
        );
        $this->tableOfContentsBuilder = new CommonMarkTableOfContentsBuilder();
    }

    protected function registerScripts(PriorityQueue $scripts): void
    {
        parent::registerScripts($scripts);

        $this->tableOfContents->registerScripts($scripts);

        $scripts->push('controller_article.ts', 0);
        $scripts->push('controller_aside.ts', 0);
        $scripts->push('controller_aside-filter.ts', 0);
        $scripts->push('controller_graphviz.ts', 0);
        $scripts->push('controller_hljs.ts', 0);
        // $scripts->push('controller_mermaid.ts', 0);
    }

    protected function registerStylesheets(PriorityQueue $stylesheets): void
    {
        parent::registerStylesheets($stylesheets);

        $stylesheets->push('docs-breadcrumbs.css', 0);
        $stylesheets->push('docs-formatted-content.css', 0);
        $stylesheets->push('docs-links-group.css', 0);
        $stylesheets->push('docs-hljs.css', 0);
        $stylesheets->push('docs-page-document.css', 0);
    }

    protected function renderBodyContent(StaticPage $staticPage): Generator
    {
        $renderedOutput = $this
            ->staticPageMarkdownParser
            ->converter
            ->convert($staticPage->content)
        ;

        $tableOfContentsLinks = $this
            ->tableOfContentsBuilder
            ->getTableOfContentsLinks($renderedOutput->getDocument())
        ;

        $lastUpdatedMTime = $staticPage->file->getMTime();
        $lastUpdatedDatetime = date(DATE_W3C, $lastUpdatedMTime);

        yield <<<'HTML'
        <div
            class="documentation"
            data-aside-filter-filtered-out-link-class="hidden"
            data-controller="aside-filter"
        >
            <form class="documentation__aside-filter">
                <input
                    autofocus
                    class="documentation__aside-filter__input"
                    data-action="input->aside-filter#onInputChange"
                    data-aside-filter-target="searchInput"
                    placeholder="Filter pages..."
                    type="search"
                >
            </form>
            <nav class="documentation__aside">
                <div
                    class="documentation__aside__links"
                    data-controller="aside"
                >
        HTML;
        yield from $this->documentsMenu->render($staticPage);
        yield <<<HTML
                </div>
            </nav>
            <article
                class="documentation__article formatted-content"
                data-controller="article"
            >
                {$renderedOutput->getContent()}
        HTML;
        yield <<<'HTML'
            </article>
        HTML;
        yield from $this->renderRelatedPageReference($staticPage);
        yield <<<HTML
            <time
                class="documentation__last-updated"
                datetime="{$lastUpdatedDatetime}"
            >
                Last updated on
                <strong>{$this->intlDateFormatter->format($lastUpdatedMTime)}</strong>
            </time>
            <nav class="breadcrumbs documentation__breadcrumbs">
        HTML;
        yield from $this->breadcrumbs->render($staticPage);
        yield '</nav>';
        yield from $this->tableOfContents->render($tableOfContentsLinks);
        yield '</div>';
    }

    protected function renderPrimaryBanner(StaticPage $staticPage): Generator
    {
        yield '';
    }

    /**
     * @return Generator<string>
     */
    private function renderRelatedPageReference(StaticPage $staticPage): Generator
    {
        $nextPage = $this->staticPageCollectionAggregate->pagesFollowers->get($staticPage, null);
        $prevPage = $this->staticPageCollectionAggregate->pagesPredecessors->get($staticPage, null);

        if (!isset($nextPage) && !isset($prevPage)) {
            return;
        }

        yield <<<'HTML'
        <div class="documentation__related-pages">
        HTML;
        if (isset($prevPage)) {
            yield <<<HTML
            <a
                class="
                    documentation__related-pages__link
                    documentation__related-pages__link--prev
                "
                href="{$prevPage->getHref()}"
            >
                <div class="documentation__related-pages__label">
                    Previous
                </div>
                <div class="documentation__related-pages__title">
                    &laquo; {$prevPage->frontMatter->title}
                </div>
                <div class="documentation__related-pages__description">
                    {$prevPage->frontMatter->description}
                </div>
            </a>
            HTML;
        }
        if (isset($nextPage)) {
            yield <<<HTML
            <a
                class="
                    documentation__related-pages__link
                    documentation__related-pages__link--next
                "
                data-turbo-preload
                href="{$nextPage->getHref()}"
            >
                <div class="documentation__related-pages__label">
                    Next
                </div>
                <div class="documentation__related-pages__title">
                    {$nextPage->frontMatter->title} &raquo;
                </div>
                <div class="documentation__related-pages__description">
                    {$nextPage->frontMatter->description}
                </div>
            </a>
            HTML;
        }
        yield <<<'HTML'
        </div>
        HTML;
    }
}
