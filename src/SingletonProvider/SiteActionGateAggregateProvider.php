<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\DecidesSiteAction;
use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Distantmagic\Resonance\SiteActionGate;
use Distantmagic\Resonance\SiteActionGateAggregate;

/**
 * @template-extends SingletonProvider<SiteActionGateAggregate>
 */
#[RequiresSingletonCollection(SingletonCollection::SiteActionGate)]
#[Singleton(provides: SiteActionGateAggregate::class)]
final readonly class SiteActionGateAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): SiteActionGateAggregate
    {
        $siteActionGateAggregate = new SiteActionGateAggregate();

        foreach ($this->collectSiteActionGates($singletons) as $deciderAttribute) {
            $siteActionGateAggregate->siteActionGates->put(
                $deciderAttribute->attribute->siteAction,
                $deciderAttribute->singleton,
            );
        }

        return $siteActionGateAggregate;
    }

    /**
     * @return iterable<SingletonAttribute<SiteActionGate,DecidesSiteAction>>
     */
    private function collectSiteActionGates(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            SiteActionGate::class,
            DecidesSiteAction::class,
        );
    }
}
