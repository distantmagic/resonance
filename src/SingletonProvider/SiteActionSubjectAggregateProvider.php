<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Can;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Distantmagic\Resonance\SiteActionSubjectAggregate;

/**
 * @template-extends SingletonProvider<SiteActionSubjectAggregate>
 */
#[Singleton(
    provides: SiteActionSubjectAggregate::class,
    requiresCollection: SingletonCollection::HttpResponder,
)]
final readonly class SiteActionSubjectAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): SiteActionSubjectAggregate
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
