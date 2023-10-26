<?php

declare(strict_types=1);

namespace Distantmagic\Docs\Template\Component;

use Distantmagic\Docs\Template\Component;
use Distantmagic\Resonance\CommonMarkTableOfContentsLink;
use Ds\PriorityQueue;
use Generator;

readonly class StaticPageDocumentTableOfContents extends Component
{
    public function __construct(
        private string $baseClassName,
        private string $outletSelector,
    ) {}

    public function registerScripts(PriorityQueue $scripts): void
    {
        $scripts->push('controller_minimap.ts', 0);
    }

    /**
     * @param Generator<CommonMarkTableOfContentsLink> $links
     *
     * @return Generator<string>
     */
    public function render(Generator $links): Generator
    {
        yield <<<HTML
        <nav
            class="{$this->baseClassName}__toc"
            data-controller="minimap"
            data-minimap-article-outlet="{$this->outletSelector}"
        >
            <div
                class="{$this->baseClassName}__toc__links"
                data-minimap-target="track"
            >
        HTML;
        yield from $this->renderTableOfContentsLinks($links);
        yield <<<'HTML'
            </div>
        </nav>
        HTML;
    }

    /**
     * @param Generator<CommonMarkTableOfContentsLink> $tableOfContentsLinks
     *
     * @return Generator<string>
     */
    private function renderTableOfContentsLinks(Generator $tableOfContentsLinks): Generator
    {
        foreach ($tableOfContentsLinks as $tableOfContentsLink) {
            yield sprintf(
                <<<'HTML'
                <a
                    class="level-%s"
                    href="#%s"
                    data-minimap-target="link"
                >
                    <div class="heading-permalink">»</div>%s
                </a>
                HTML,
                $tableOfContentsLink->level,
                $tableOfContentsLink->slug,
                $tableOfContentsLink->text,
            );
        }
    }
}
