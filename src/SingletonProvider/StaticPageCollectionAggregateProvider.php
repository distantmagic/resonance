<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Distantmagic\Resonance\StaticPageAggregate;
use Distantmagic\Resonance\StaticPageCollectionAggregate;

/**
 * @template-extends SingletonProvider<StaticPageCollectionAggregate>
 */
#[Singleton(provides: StaticPageCollectionAggregate::class)]
final readonly class StaticPageCollectionAggregateProvider extends SingletonProvider
{
    public function __construct(private StaticPageAggregate $staticPageAggregate) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): StaticPageCollectionAggregate
    {
        $staticPageCollectionAggregate = new StaticPageCollectionAggregate($this->staticPageAggregate);

        foreach ($this->staticPageAggregate->staticPages as $staticPage) {
            $staticPageCollectionAggregate->addToCollections($staticPage);
        }

        $staticPageCollectionAggregate->sortCollections();

        return $staticPageCollectionAggregate;
    }
}
