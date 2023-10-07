<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use Ds\Set;

readonly class StaticPageCollectionAggregate
{
    /**
     * @var Set<string>
     */
    public Set $unusedCollections;

    /**
     * @var Map<string, StaticPageCollection>
     */
    private Map $collections;

    public function __construct()
    {
        $this->collections = new Map();
        $this->unusedCollections = new Set();
    }

    public function addToCollections(StaticPage $staticPage): void
    {
        foreach ($staticPage->frontMatter->collections as $frontMatterCollection) {
            $this->addToCollection($staticPage, $frontMatterCollection);
        }
    }

    /**
     * @param Map<string, StaticPage> $staticPages
     */
    public function sortCollections(Map $staticPages): void
    {
        foreach ($this->collections as $collection) {
            $collection->sort($staticPages);
        }
    }

    public function useCollection(string $collectionName): StaticPageCollection
    {
        if (!$this->collections->hasKey($collectionName)) {
            throw new StaticPageReferenceException('Tried to use an empty collection. Please add elements to the collection or do not use it: '.$collectionName);
        }

        $this->unusedCollections->remove($collectionName);

        return $this->collections->get($collectionName);
    }

    private function addToCollection(
        StaticPage $staticPage,
        FrontMatterCollectionReference $frontMatterCollection,
    ): void {
        $this
            ->createGetCollection($frontMatterCollection->name)
            ->add($staticPage, $frontMatterCollection)
        ;
    }

    private function createGetCollection(string $collectionName): StaticPageCollection
    {
        if ($this->collections->hasKey($collectionName)) {
            return $this->collections->get($collectionName);
        }

        $collection = new StaticPageCollection();

        $this->collections->put($collectionName, $collection);
        $this->unusedCollections->add($collectionName);

        return $collection;
    }
}
