<?php

declare(strict_types=1);

namespace Distantmagic\Docs\Template\StaticPageLayout\Turbo;

use Distantmagic\Docs\Template\Component\StaticPageBreadcrumbs;
use Distantmagic\Docs\Template\StaticPageLayout\Turbo;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\StaticPageLayout;
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
#[StaticPageLayout('dm:tutorial')]
readonly class Tutorial extends Turbo
{
    private StaticPageBreadcrumbs $breadcrumbs;
    private IntlDateFormatter $intlDateFormatter;

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
        $this->intlDateFormatter = new IntlDateFormatter(
            'en',
            IntlDateFormatter::LONG,
            IntlDateFormatter::NONE,
        );
    }

    protected function registerScripts(PriorityQueue $scripts): void
    {
        parent::registerScripts($scripts);

        $scripts->push('controller_graphviz.ts', 0);
        $scripts->push('controller_hljs.ts', 0);
    }

    protected function registerStylesheets(PriorityQueue $stylesheets): void
    {
        parent::registerStylesheets($stylesheets);

        $stylesheets->push('docs-breadcrumbs.css', 0);
        $stylesheets->push('docs-formatted-content.css', 0);
        $stylesheets->push('docs-links-group.css', 0);
        $stylesheets->push('docs-hljs.css', 0);
        $stylesheets->push('docs-page-tutorial.css', 0);
    }

    protected function renderBodyContent(StaticPage $staticPage): Generator
    {
        $renderedOutput = $this
            ->staticPageMarkdownParser
            ->converter
            ->convert($staticPage->content)
        ;

        $lastUpdatedMTime = $staticPage->file->getMTime();
        $lastUpdatedDatetime = date(DATE_W3C, $lastUpdatedMTime);

        yield <<<'HTML'
        <div class="tutorial">
            <nav class="breadcrumbs tutorial__breadcrumbs">
        HTML;
        yield from $this->breadcrumbs->render($staticPage);
        yield <<<HTML
            </nav>
            <div class="tutorial__content">
                <hgroup class="tutorial__header">
                    <h1 class="tutorial__title">
                        {$staticPage->frontMatter->title}
                    </h1>
                    <h2 class="tutorial__sub-title">
                        {$staticPage->frontMatter->description}
                    </h2>
                    <time
                        class="tutorial__last-updated"
                        datetime="{$lastUpdatedDatetime}"
                    >
                        Last updated on
                        <strong>{$this->intlDateFormatter->format($lastUpdatedMTime)}</strong>
                    </time>
                </hgroup>
                <div class="formatted-content tutorial__readme">
                    {$renderedOutput}
                </div>
            </div>
        </div>
        HTML;
    }
}
