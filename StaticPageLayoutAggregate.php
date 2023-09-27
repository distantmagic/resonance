<?php

declare(strict_types=1);

namespace Resonance;

use Ds\Map;
use Generator;
use Resonance\Template\StaticPageLayout\Turbo\Document;
use Resonance\Template\StaticPageLayout\Turbo\Page;
use Throwable;

readonly class StaticPageLayoutAggregate
{
    private TemplateStaticPageLayoutInterface $document;
    private TemplateStaticPageLayoutInterface $page;

    /**
     * @param Map<string,StaticPage> $staticPages
     */
    public function __construct(
        Map $staticPages,
        StaticPageCollectionAggregate $staticPageCollectionAggregate,
        StaticPageContentRenderer $staticPageContentRenderer,
    ) {
        $templateFilters = new TemplateFilters();

        $this->document = new Document(
            $staticPages,
            $staticPageCollectionAggregate,
            $staticPageContentRenderer,
            $templateFilters,
        );
        $this->page = new Page(
            $staticPages,
            $staticPageCollectionAggregate,
            $staticPageContentRenderer,
            $templateFilters,
        );
    }

    /**
     * @return Generator<string>
     */
    public function render(StaticPage $staticPage): Generator
    {
        try {
            yield from $this
                ->selectLayout($staticPage->frontMatter->layout)
                ->renderStaticPage($staticPage)
            ;
        } catch (Throwable $exception) {
            throw new StaticPageRenderingException(
                'Error occurred while rendering the page: '.$staticPage->getBasename(),
                0,
                $exception
            );
        }
    }

    public function selectLayout(StaticPageLayoutHandler $layout): TemplateStaticPageLayoutInterface
    {
        return match ($layout) {
            StaticPageLayoutHandler::Document => $this->document,
            StaticPageLayoutHandler::Page => $this->page,
        };
    }
}
