<?php

declare(strict_types=1);

namespace Resonance;

use Ds\Map;
use Ds\Set;
use Generator;
use IteratorAggregate;
use Tree\Node\Node;

/**
 * @template-implements IteratorAggregate<Node>
 */
readonly class StaticPageForestGenerator implements IteratorAggregate
{
    /**
     * @param Map<string, StaticPage> $staticPages
     * @param iterable<StaticPage>    $staticPagesCollection
     */
    public function __construct(
        private Map $staticPages,
        private iterable $staticPagesCollection
    ) {}

    /**
     * @return Generator<Node> nodes with 0 depth
     */
    public function getIterator(): Generator
    {
        /**
         * @var Set<Node> $heads
         */
        $heads = new Set();

        /**
         * @var Map<StaticPage, Node> $nodes
         */
        $nodes = new Map();

        foreach ($this->staticPagesCollection as $staticPage) {
            $node = new Node($staticPage);
            $nodes->put($staticPage, $node);

            if (empty($staticPage->frontMatter->parent)) {
                $heads->add($node);
            }
        }

        foreach ($this->staticPagesCollection as $staticPage) {
            $parentBasename = $staticPage->frontMatter->parent;

            if (!empty($parentBasename)) {
                $nodes
                    ->get($this->getStaticPage($parentBasename))
                    ->addChild($nodes->get($staticPage))
                ;
            }
        }

        foreach ($heads as $head) {
            yield $head;
        }
    }

    private function getStaticPage(string $pageBasename): StaticPage
    {
        if (!$this->staticPages->hasKey($pageBasename)) {
            throw new StaticPageReferenceException(
                'Could not find a page reference while building static pages tree: '.$pageBasename
            );
        }

        return $this->staticPages->get($pageBasename);
    }
}
