<?php

declare(strict_types=1);

namespace Resonance\Template\StaticPageLayout\Turbo;

use Ds\Map;
use Generator;
use Resonance\StaticPage;
use Resonance\StaticPageCollectionAggregate;
use Resonance\StaticPageContentRenderer;
use Resonance\Template\StaticPageLayout\Turbo;
use Resonance\TemplateFilters;

readonly class Page extends Turbo
{
    /**
     * @param Map<string,StaticPage> $staticPages
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
    }

    protected function renderBodyContent(StaticPage $staticPage): Generator
    {
        yield $this->staticPageContentRenderer->renderContent($staticPage);
    }
}
