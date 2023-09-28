<?php

declare(strict_types=1);

namespace Resonance\Template\Component;

use Ds\PriorityQueue;
use Generator;
use League\CommonMark\Output\RenderedContentInterface;
use Resonance\CommonMarkRenderedContentWithTableOfContentsLinks;
use Resonance\CommonMarkTableOfContentsLink;
use Resonance\Template\Component;

readonly class StaticPageDocumentTableOfContents extends Component
{
    public function registerScripts(PriorityQueue $scripts): void
    {
        $scripts->push('controller_minimap.ts', 0);
    }

    /**
     * @return Generator<string>
     */
    public function render(
        CommonMarkRenderedContentWithTableOfContentsLinks|RenderedContentInterface $renderedContent
    ): Generator {
        if (!($renderedContent instanceof CommonMarkRenderedContentWithTableOfContentsLinks)) {
            return;
        }

        yield <<<'HTML'
        <nav
            class="documentation__toc"
            data-controller="minimap"
            data-minimap-article-outlet=".documentation__article"
        >
            <div
                class="documentation__toc__links"
                data-minimap-target="track"
            >
        HTML;
        yield from $this->renderTableOfContentsLinks($renderedContent->tableOfContentsLinks);
        yield <<<'HTML'
            </div>
        </nav>
        HTML;
    }

    /**
     * @param array<CommonMarkTableOfContentsLink> $tableOfContentsLinks
     *
     * @return Generator<string>
     */
    private function renderTableOfContentsLinks(array $tableOfContentsLinks): Generator
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
