<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Attribute\StaticPageLayout;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Distantmagic\Resonance\StaticPageLayoutAggregate;
use Distantmagic\Resonance\StaticPageLayoutInterface;

/**
 * @template-extends SingletonProvider<StaticPageLayoutAggregate>
 */
#[RequiresSingletonCollection(SingletonCollection::StaticPageLayout)]
#[Singleton(provides: StaticPageLayoutAggregate::class)]
final readonly class StaticPageLayoutAggregateProvider extends SingletonProvider
{
    public function __construct() {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): StaticPageLayoutAggregate
    {
        $staticPageLayoutAggregate = new StaticPageLayoutAggregate();

        foreach ($this->collectLayouts($singletons) as $layoutAttribute) {
            $staticPageLayoutAggregate->staticPageLayout->put(
                $layoutAttribute->attribute->name,
                $layoutAttribute->singleton,
            );
        }

        return $staticPageLayoutAggregate;
    }

    /**
     * @return iterable<SingletonAttribute<StaticPageLayoutInterface,StaticPageLayout>>
     */
    private function collectLayouts(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            StaticPageLayoutInterface::class,
            StaticPageLayout::class,
        );
    }
}
