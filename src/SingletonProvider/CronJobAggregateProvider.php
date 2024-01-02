<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\RequiresSingletonCollection;
use Distantmagic\Resonance\Attribute\ScheduledWithCron;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\CronJobAggregate;
use Distantmagic\Resonance\CronJobInterface;
use Distantmagic\Resonance\CronRegisteredJob;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonAttribute;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<CronJobAggregate>
 */
#[RequiresSingletonCollection(SingletonCollection::CronJob)]
#[Singleton(provides: CronJobAggregate::class)]
final readonly class CronJobAggregateProvider extends SingletonProvider
{
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): CronJobAggregate
    {
        $cronJobAggregate = new CronJobAggregate();

        foreach ($this->collectCronJobs($singletons) as $cronJobAttribute) {
            $cronRegisteredJob = new CronRegisteredJob(
                $cronJobAttribute->singleton,
                $cronJobAttribute->attribute->expression,
                $cronJobAttribute->attribute->name ?? $cronJobAttribute->singleton::class,
            );
            $cronJobAggregate->cronJobs->add($cronRegisteredJob);
        }

        return $cronJobAggregate;
    }

    /**
     * @return iterable<SingletonAttribute<CronJobInterface,ScheduledWithCron>>
     */
    private function collectCronJobs(SingletonContainer $singletons): iterable
    {
        return $this->collectAttributes(
            $singletons,
            CronJobInterface::class,
            ScheduledWithCron::class,
        );
    }
}
