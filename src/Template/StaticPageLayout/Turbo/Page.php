<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Template\StaticPageLayout\Turbo;

use Distantmagic\Resonance\EsbuildMeta;
use Distantmagic\Resonance\StaticPage;
use Distantmagic\Resonance\StaticPageCollectionAggregate;
use Distantmagic\Resonance\StaticPageContentRenderer;
use Distantmagic\Resonance\Template\StaticPageLayout\Turbo;
use Distantmagic\Resonance\TemplateFilters;
use Ds\Map;
use Generator;

readonly class Page extends Turbo
{
    /**
     * @param Map<string,StaticPage> $staticPages
     */
    public function __construct(
        EsbuildMeta $esbuildMeta,
        Map $staticPages,
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
    }

    protected function renderBodyContent(StaticPage $staticPage): Generator
    {
        yield $this->staticPageContentRenderer->renderContent($staticPage);
    }
}
