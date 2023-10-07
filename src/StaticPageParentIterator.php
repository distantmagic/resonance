<?php

declare(strict_types=1);

namespace Resonance;

use Ds\Map;
use Ds\Set;
use Generator;
use IteratorAggregate;

/**
 * @template-implements IteratorAggregate<StaticPage>
 */
readonly class StaticPageParentIterator implements IteratorAggregate
{
    /**
     * @var Set<StaticPage> $parents
     */
    private Set $parents;

    /**
     * @param Map<string, StaticPage> $staticPages
     * @param null|Set<StaticPage>    $parents
     */
    public function __construct(
        private Map $staticPages,
        private StaticPage $staticPage,
        ?Set $parents = null,
    ) {
        $this->parents = $parents ?? new Set();
    }

    /**
     * @return Generator<StaticPage>
     */
    public function getIterator(): Generator
    {
        if ($this->parents->contains($this->staticPage)) {
            throw new StaticPageReferenceException(
                'Cyclic dependency between parent pages: '.$this->listStaticDependencies()
            );
        }

        $this->parents->add($this->staticPage);

        yield $this->staticPage;

        $parentPageName = $this->staticPage->frontMatter->parent;

        if (is_null($parentPageName)) {
            return;
        }

        if (!$this->staticPages->hasKey($parentPageName)) {
            throw new StaticPageReferenceException('Parent page does not exist: '.$parentPageName);
        }

        yield from new self(
            $this->staticPages,
            $this->staticPages->get($parentPageName),
            $this->parents,
        );
    }

    private function listStaticDependencies(): string
    {
        $ret = [];

        foreach ($this->parents as $parent) {
            $ret[] = $parent->getBasename();
        }

        $ret[] = $this->staticPage->getBasename();

        /**
         * @var array<string> $ret
         */
        return implode(' -> ', $ret);
    }
}
