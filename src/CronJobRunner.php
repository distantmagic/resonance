<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Swoole\Process;

#[Singleton]
readonly class CronJobRunner
{
    public function __construct(private LoggerInterface $logger) {}

    public function runCronJob(CronRegisteredJob $cronRegisteredJob): void
    {
        $process = new Process(
            enable_coroutine: true,
            callback: static function () use ($cronRegisteredJob) {
                $cronRegisteredJob->cronJob->onCronTick();
            },
        );
        $process->name($cronRegisteredJob->name);

        $this->logger->debug(sprintf(
            'cron_job_start(%s)',
            $cronRegisteredJob->name,
        ));

        if (!$process->start()) {
            throw new RuntimeException('Unable to start CRON job: '.$cronRegisteredJob->name);
        }
    }
}
