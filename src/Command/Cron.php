<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\CoroutineCommand;
use Distantmagic\Resonance\CronJobAggregate;
use Distantmagic\Resonance\SwooleConfiguration;
use Distantmagic\Resonance\TickTimerScheduler;
use Psr\Log\LoggerInterface;
use Swoole\Event;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[ConsoleCommand(
    name: 'cron',
    description: 'Start CRON scheduler'
)]
final class Cron extends CoroutineCommand
{
    public function __construct(
        private readonly CronJobAggregate $cronJobAggregate,
        private readonly LoggerInterface $logger,
        SwooleConfiguration $swooleConfiguration,
        private readonly TickTimerScheduler $tickTimerScheduler,
    ) {
        parent::__construct($swooleConfiguration);
    }

    protected function executeInCoroutine(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->info('cron_scheduler_start()');

        foreach ($this->cronJobAggregate->cronJobs as $cronJob) {
            $this->logger->debug(sprintf('cron_register_job(%s)', $cronJob->name));
        }

        $this->tickTimerScheduler->start();

        Event::wait();

        return Command::SUCCESS;
    }
}
