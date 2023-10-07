<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use Ds\Set;
use Distantmagic\Resonance\StaticPageFileException\DuplicateCollectionReferenceException;

readonly class StaticPageCollection
{
    /**
     * @var Set<StaticPage>
     */
    public Set $staticPages;

    /**
     * @var Map<string,string>
     */
    private Map $followers;

    public function __construct()
    {
        $this->followers = new Map();
        $this->staticPages = new Set();
    }

    public function add(
        StaticPage $staticPage,
        FrontMatterCollectionReference $frontMatterCollection,
    ): void {
        if ($this->staticPages->contains($staticPage)) {
            throw new DuplicateCollectionReferenceException($staticPage, $frontMatterCollection);
        }

        if (is_string($frontMatterCollection->next)) {
            $this->followers->put(
                $staticPage->getBasename(),
                $frontMatterCollection->next,
            );
        }

        $this->staticPages->add($staticPage);
    }

    /**
     * @param Map<string, StaticPage> $staticPages
     */
    public function sort(Map $staticPages): void
    {
        foreach ($this->followers as $prev => $next) {
            $this->assertPageExists($staticPages, $prev);
            $this->assertPageExists($staticPages, $next);
        }

        $this->staticPages->sort(function (StaticPage $a, StaticPage $b) {
            $aPriority = $this->getPriority($a->getBasename());
            $bPriority = $this->getPriority($b->getBasename());

            $priorityCompare = $bPriority <=> $aPriority;

            if (0 === $priorityCompare) {
                return $a->frontMatter->title <=> $b->frontMatter->title;
            }

            return $priorityCompare;
        });
    }

    /**
     * @param Map<string, StaticPage> $staticPages
     */
    private function assertPageExists(Map $staticPages, string $basename): void
    {
        if (!$staticPages->hasKey($basename)) {
            throw new StaticPageReferenceException('Static page used in the "collection.next" field does not exist: '.$basename);
        }
    }

    /**
     * @param Set<string> $previous
     */
    private function getPriority(string $basename, int $priority = 0, Set $previous = new Set()): int
    {
        if (!$this->followers->hasKey($basename)) {
            return $priority;
        }

        if ($previous->contains($basename)) {
            throw new StaticPageReferenceException(sprintf(
                'Cyclic "next" page references: "%s" -> "%s"',
                $previous->join('" -> "'),
                $basename
            ));
        }

        $previous->add($basename);

        return $this->getPriority(
            $this->followers->get($basename),
            $priority + 1,
            $previous,
        );
    }
}
