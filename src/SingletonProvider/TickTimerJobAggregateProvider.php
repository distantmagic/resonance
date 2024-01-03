<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\ScheduledWithTickTimer;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Distantmagic\Resonance\TickTimerJobAggregate;
use Distantmagic\Resonance\TickTimerJobInterface;
use Distantmagic\Resonance\TickTimerRegisteredJob;

/**
 * @template-extends SingletonProvider<TickTimerJobAggregate>
 */
#[RequiresSingletonCollection(SingletonCollection::TickTimerJob)]
#[Singleton(provides: TickTimerJobAggregate::class)]
final readonly class TickTimerJobAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): TickTimerJobAggregate
    {
        $tickTimerJobAggregate = new TickTimerJobAggregate();

        foreach ($this->collectTickTimerJobs($singletons) as $tickTimerJobAttribute) {
            if ($tickTimerJobAttribute->singleton->shouldRegister()) {
                $tickTimerRegisteredJob = new TickTimerRegisteredJob(
                    $tickTimerJobAttribute->singleton,
                    $tickTimerJobAttribute->attribute->interval,
                );
                $tickTimerJobAggregate->tickTimerJobs->add($tickTimerRegisteredJob);
            }
        }

        return $tickTimerJobAggregate;
    }

    /**
     * @return iterable<SingletonAttribute<TickTimerJobInterface,ScheduledWithTickTimer>>
     */
    private function collectTickTimerJobs(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            TickTimerJobInterface::class,
            ScheduledWithTickTimer::class,
        );
    }
}
