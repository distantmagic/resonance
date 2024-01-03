<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Psr\Log\LoggerInterface;
use RuntimeException;

use function Swoole\Coroutine\go;

#[Singleton]
readonly class CronJobRunner
{
    public function __construct(private LoggerInterface $logger) {}

    public function runCronJob(CronRegisteredJob $cronRegisteredJob): void
    {
        $cid = go(function () use ($cronRegisteredJob) {
            $this->logger->info(sprintf('cron_job_start(%s)', $cronRegisteredJob->name));
            $cronRegisteredJob->cronJob->onCronTick();
        });

        if (!is_int($cid)) {
            throw new RuntimeException('Unable to start CRON job: '.$cronRegisteredJob->name);
        }
    }
}
