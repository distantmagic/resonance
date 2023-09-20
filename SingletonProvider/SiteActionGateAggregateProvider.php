<?php

declare(strict_types=1);

namespace Resonance\SingletonProvider;

use Resonance\Attribute\DecidesSiteAction;
use Resonance\Attribute\Singleton;
use Resonance\SingletonAttribute;
use Resonance\SingletonCollection;
use Resonance\SingletonContainer;
use Resonance\SingletonProvider;
use Resonance\SiteActionGate;
use Resonance\SiteActionGateAggregate;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

/**
 * @template-extends SingletonProvider<SiteActionGateAggregate>
 */
#[Singleton(
    provides: SiteActionGateAggregate::class,
    requiresCollection: SingletonCollection::SiteAction,
)]
final readonly class SiteActionGateAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, ?ConsoleOutputInterface $output = null): SiteActionGateAggregate
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
