<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use Generator;
use Distantmagic\Resonance\Template\StaticPageLayout\Turbo\Document;
use Distantmagic\Resonance\Template\StaticPageLayout\Turbo\Page;
use Throwable;

readonly class StaticPageLayoutAggregate
{
    private TemplateStaticPageLayoutInterface $document;
    private TemplateStaticPageLayoutInterface $page;

    /**
     * @param Map<string,StaticPage>     $staticPages
     * @param Map<StaticPage,StaticPage> $staticPagesFollowers
     * @param Map<StaticPage,StaticPage> $staticPagesPredecessors
     */
    public function __construct(
        EsbuildMeta $esbuildMeta,
        Map $staticPages,
        Map $staticPagesFollowers,
        Map $staticPagesPredecessors,
        StaticPageCollectionAggregate $staticPageCollectionAggregate,
        StaticPageContentRenderer $staticPageContentRenderer,
    ) {
        $templateFilters = new TemplateFilters();

        $this->document = new Document(
            $esbuildMeta,
            $staticPages,
            $staticPagesFollowers,
            $staticPagesPredecessors,
            $staticPageCollectionAggregate,
            $staticPageContentRenderer,
            $templateFilters,
        );
        $this->page = new Page(
            $esbuildMeta,
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
