<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Template\StaticPageLayout\Turbo;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\StaticPageLayout;
use Distantmagic\Resonance\EsbuildMetaBuilder;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\StaticPage;
use Distantmagic\Resonance\StaticPageAggregate;
use Distantmagic\Resonance\StaticPageCollectionAggregate;
use Distantmagic\Resonance\StaticPageConfiguration;
use Distantmagic\Resonance\StaticPageContentRenderer;
use Distantmagic\Resonance\Template\StaticPageLayout\Turbo;
use Distantmagic\Resonance\TemplateFilters;
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

    protected function renderBodyContent(StaticPage $staticPage): Generator
    {
        yield $this->staticPageContentRenderer->renderContent($staticPage);
    }
}
