<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Command;
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
final class Cron extends Command
{
    public function __construct(
        private LoggerInterface $logger,
        private SwooleConfiguration $swooleConfiguration,
        private TickTimerScheduler $tickTimerScheduler,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        swoole_async_set([
            'enable_coroutine' => false,
            'log_level' => $this->swooleConfiguration->logLevel,
        ]);

        $this->logger->info('cron_scheduler_start()');

        $this->tickTimerScheduler->start();

        Event::wait();

        return Command::SUCCESS;
    }
}
