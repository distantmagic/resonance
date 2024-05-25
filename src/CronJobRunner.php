<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Psr\Log\LoggerInterface;

#[Singleton]
readonly class CronJobRunner
{
    public function __construct(
        private CoroutineDriverInterface $coroutineDriver,
        private LoggerInterface $logger
    ) {}

    public function runCronJob(CronRegisteredJob $cronRegisteredJob): void
    {
        $this->coroutineDriver->go(function () use ($cronRegisteredJob): void {
            $this->logger->info(sprintf('cron_job_start(%s)', $cronRegisteredJob->name));
            $cronRegisteredJob->cronJob->onCronTick();
        });
    }
}
