<?php

declare(strict_types=1);

namespace Distantmagic\Docs\Template\StaticPageLayout\Turbo;

use Distantmagic\Docs\Template\StaticPageLayout\Turbo;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\StaticPageLayout;
use Distantmagic\Resonance\EsbuildMetaBuilder;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\StaticPage;
use Distantmagic\Resonance\StaticPageAggregate;
use Distantmagic\Resonance\StaticPageCollectionAggregate;
use Distantmagic\Resonance\StaticPageConfiguration;
use Distantmagic\Resonance\StaticPageContentRenderer;
use Distantmagic\Resonance\TemplateFilters;
use Ds\PriorityQueue;
use Generator;

#[Singleton(collection: SingletonCollection::StaticPageLayout)]
#[StaticPageLayout('dm:page')]
readonly class Page extends Turbo
{
    public function __construct(
        EsbuildMetaBuilder $esbuildMetaBuilder,
        StaticPageAggregate $staticPageAggregate,
        StaticPageCollectionAggregate $staticPageCollectionAggregate,
        StaticPageConfiguration $staticPageConfiguration,
        private StaticPageContentRenderer $staticPageContentRenderer,
        TemplateFilters $filters,
    ) {
        parent::__construct(
            $esbuildMetaBuilder,
            $staticPageAggregate->staticPages,
            $staticPageCollectionAggregate,
            $staticPageConfiguration,
            $filters,
        );
    }

    protected function registerScripts(PriorityQueue $scripts): void
    {
        parent::registerScripts($scripts);

        $scripts->push('controller_hljs.ts', 0);
    }

    protected function registerStylesheets(PriorityQueue $stylesheets): void
    {
        parent::registerStylesheets($stylesheets);

        $stylesheets->push('docs-formatted-content.css', 0);
        $stylesheets->push('docs-hljs.css', 0);
    }

    protected function renderBodyContent(StaticPage $staticPage): Generator
    {
        yield $this->staticPageContentRenderer->renderContent($staticPage);
    }
}
