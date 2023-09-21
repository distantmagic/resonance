<?php

declare(strict_types=1);

namespace Resonance\SingletonProvider;

use Resonance\Attribute\Can;
use Resonance\Attribute\Singleton;
use Resonance\HttpResponderInterface;
use Resonance\SingletonAttribute;
use Resonance\SingletonCollection;
use Resonance\SingletonContainer;
use Resonance\SingletonProvider;
use Resonance\SiteActionSubjectAggregate;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

/**
 * @template-extends SingletonProvider<SiteActionSubjectAggregate>
 */
#[Singleton(
    provides: SiteActionSubjectAggregate::class,
    requiresCollection: SingletonCollection::HttpResponder,
)]
final readonly class SiteActionSubjectAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, ?ConsoleOutputInterface $output = null): SiteActionSubjectAggregate
    {
        $siteActionGateAggregate = new SiteActionSubjectAggregate();

        foreach ($this->collectResponders($singletons) as $deciderAttribute) {
            $siteActionGateAggregate->registerSiteAction(
                $deciderAttribute->singleton,
                $deciderAttribute->attribute->siteAction,
            );
        }

        return $siteActionGateAggregate;
    }

    /**
     * @return iterable<SingletonAttribute<HttpResponderInterface,Can>>
     */
    private function collectResponders(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            HttpResponderInterface::class,
            Can::class,
        );
    }
}
