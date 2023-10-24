<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;

readonly class StaticPageCollectionAggregate
{
    /**
     * @var Map<StaticPage, StaticPage>
     */
    public Map $pagesFollowers;

    /**
     * @var Map<StaticPage, StaticPage>
     */
    public Map $pagesPredecessors;

    /**
     * @var Map<string, StaticPageCollection>
     */
    private Map $collections;

    public function __construct(
        private StaticPageAggregate $staticPageAggregate,
    ) {
        $this->collections = new Map();
        $this->pagesFollowers = new Map();
        $this->pagesPredecessors = new Map();
    }

    public function addToCollections(StaticPage $staticPage): void
    {
        foreach ($staticPage->frontMatter->collections as $frontMatterCollection) {
            $this->addToCollection($staticPage, $frontMatterCollection);
        }

        $nextBasename = $staticPage->frontMatter->next;

        if (!isset($nextBasename)) {
            return;
        }

        if (!$this->staticPageAggregate->staticPages->hasKey($nextBasename)) {
            throw new StaticPageReferenceException('Static Page referenced in the "next" field does not exist: '.$nextBasename);
        }

        $nextStaticPage = $this->staticPageAggregate->staticPages->get($nextBasename);

        $this->pagesFollowers->put($staticPage, $nextStaticPage);
        $this->pagesPredecessors->put($nextStaticPage, $staticPage);
    }

    public function sortCollections(): void
    {
        foreach ($this->collections as $collection) {
            $collection->sort($this->staticPageAggregate->staticPages);
        }
    }

    public function useCollection(string $collectionName): StaticPageCollection
    {
        if (!$this->collections->hasKey($collectionName)) {
            throw new StaticPageReferenceException('Tried to use an empty collection. Please add elements to the collection or do not use it: '.$collectionName);
        }

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

        return $collection;
    }
}
