<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Template\Component;

use Ds\Map;
use Generator;
use Distantmagic\Resonance\StaticPage;
use Distantmagic\Resonance\StaticPageCollectionAggregate;
use Distantmagic\Resonance\StaticPageForestGenerator;
use Distantmagic\Resonance\Template\Component;
use Tree\Node\Node;

readonly class StaticPageDocumentsMenu extends Component
{
    /**
     * @param Map<string, StaticPage> $staticPages
     */
    public function __construct(
        private Map $staticPages,
        private StaticPageCollectionAggregate $staticPageCollectionAggregate,
        private int $minimumDepth = 0,
    ) {}

    /**
     * @return Generator<string>
     */
    public function render(StaticPage $currentPage): Generator
    {
        $documents = $this
            ->staticPageCollectionAggregate
            ->useCollection('documents')
            ->staticPages
        ;

        foreach (new StaticPageForestGenerator($this->staticPages, $documents) as $node) {
            yield from $this->renderDocumentsMenuNode($currentPage, $node, 0);
        }
    }

    /**
     * @return Generator<string>
     */
    private function renderDocumentsMenuChildNodes(StaticPage $currentPage, Node $node, int $depth): Generator
    {
        /**
         * @var Node $childNode
         */
        foreach ($node->getChildren() as $childNode) {
            yield from $this->renderDocumentsMenuNode($currentPage, $childNode, $depth);
        }
    }

    /**
     * @return Generator<string>
     */
    private function renderDocumentsMenuNode(StaticPage $currentPage, Node $node, int $depth): Generator
    {
        if ($depth < $this->minimumDepth) {
            yield from $this->renderDocumentsMenuChildNodes($currentPage, $node, $depth + 1);
        } else {
            yield from $this->renderDocumentsMenuNodeLinks($currentPage, $node, $depth);
        }
    }

    /**
     * @return Generator<string>
     */
    private function renderDocumentsMenuNodeLinks(StaticPage $currentPage, Node $node, int $depth): Generator
    {
        yield sprintf('<div class="documentation__aside__links-group level-%s">', $depth);

        /**
         * @var StaticPage $staticPage
         */
        $staticPage = $node->getValue();

        $activeClass = $currentPage->is($staticPage) ? 'active' : '';

        yield sprintf(
            '<a class="%s" href="%s">%s</a>',
            $activeClass,
            $staticPage->getHref(),
            $staticPage->frontMatter->title,
        );
        yield from $this->renderDocumentsMenuChildNodes($currentPage, $node, $depth + 1);
        yield '</div>';
    }
}
