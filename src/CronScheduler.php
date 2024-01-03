<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use DateTimeImmutable;
use DateTimeInterface;
use Distantmagic\Resonance\Attribute\ScheduledWithTickTimer;
use Distantmagic\Resonance\Attribute\Singleton;
use WeakMap;

#[ScheduledWithTickTimer(1)]
#[Singleton(collection: SingletonCollection::TickTimerJob)]
readonly class CronScheduler implements TickTimerJobInterface
{
    /**
     * @var WeakMap<CronRegisteredJob,DateTimeInterface>
     */
    public WeakMap $schedule;

    public function __construct(
        private CronJobAggregate $cronJobAggregate,
        private CronJobRunner $cronJobRunner,
    ) {
        /**
         * @var WeakMap<CronRegisteredJob,DateTimeInterface>
         */
        $this->schedule = new WeakMap();
    }

    public function onTimerTick(): void
    {
        $now = new DateTimeImmutable();

        foreach ($this->cronJobAggregate->cronJobs as $cronRegisteredJob) {
            if (!$this->schedule->offsetExists($cronRegisteredJob)) {
                $this->schedule->offsetSet(
                    $cronRegisteredJob,
                    $cronRegisteredJob->expression->getNextRunDate(),
                );
            }
        }

        /**
         * @var CronRegisteredJob $cronRegisteredJob
         * @var DateTimeInterface $scheduledDate
         */
        foreach ($this->schedule as $cronRegisteredJob => $scheduledDate) {
            if ($scheduledDate <= $now) {
                try {
                    $this->cronJobRunner->runCronJob($cronRegisteredJob);
                } finally {
                    $this->schedule->offsetUnset($cronRegisteredJob);
                }
            }
        }
    }

    public function shouldRegister(): bool
    {
        return !$this->cronJobAggregate->cronJobs->isEmpty();
    }
}
