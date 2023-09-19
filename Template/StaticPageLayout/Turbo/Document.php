<?php

declare(strict_types=1);

namespace Resonance\Template\StaticPageLayout\Turbo;

use App\Template\Component\StaticPageBreadcrumbs;
use App\Template\Component\StaticPageDocumentsMenu;
use App\Template\Component\StaticPageDocumentTableOfContents;
use Ds\Map;
use Generator;
use Resonance\CommonMarkRenderedContentWithTableOfContentsLinks;
use Resonance\StaticPage;
use Resonance\StaticPageCollectionAggregate;
use Resonance\StaticPageContentRenderer;
use Resonance\Template\StaticPageLayout\Turbo;
use Resonance\TemplateFilters;

readonly class Document extends Turbo
{
    private StaticPageBreadcrumbs $breadcrumbs;
    private StaticPageDocumentsMenu $documentsMenu;
    private StaticPageDocumentTableOfContents $tableOfContents;

    /**
     * @param Map<string, StaticPage> $staticPages
     */
    public function __construct(
        Map $staticPages,
        StaticPageCollectionAggregate $staticPageCollectionAggregate,
        private StaticPageContentRenderer $staticPageContentRenderer,
        TemplateFilters $filters,
    ) {
        parent::__construct(
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
            </article>
            <nav class="documentation__breadcrumbs">
        HTML;
        yield from $this->breadcrumbs->render($staticPage);
        yield '</nav>';
        yield from $this->tableOfContents->render($renderedContent);
        yield '</div>';
    }
}
