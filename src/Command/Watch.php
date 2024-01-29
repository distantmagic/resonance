<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\ApplicationConfiguration;
use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\InotifyIterator;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Swoole\Process;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[ConsoleCommand(
    name: 'watch:command',
    description: 'Watch project files for changes (requires inotify)'
)]
final class Watch extends Command
{
    private ?Process $process = null;

    public function __construct(
        private readonly ApplicationConfiguration $applicationConfiguration,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'What command do you want to run?');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!extension_loaded('inotify')) {
            throw new RuntimeException('You need to install "inotify" extension');
        }

        /**
         * @var string $childCommandName
         */
        $childCommandName = $input->getArgument('name');

        $directories = [
            DM_APP_ROOT,
            DM_APP_ROOT.'/../config.ini',
            $this->applicationConfiguration->esbuildMetafile,
            DM_RESONANCE_ROOT,
        ];

        $this->restartChildCommand($childCommandName);

        foreach (new InotifyIterator($directories) as $event) {
            $this->logger->info(sprintf('watch_file_changed(%s)', $event['name']));

            $this->restartChildCommand($childCommandName);
        }

        return Command::SUCCESS;
    }

    private function restartChildCommand(string $childCommandName): void
    {
        $this->logger->info(sprintf('watch_restart(%s)', $childCommandName));

        /**
         * @var null|int $pid
         */
        $pid = $this->process?->pid;

        if (is_int($pid)) {
            /**
             * @psalm-suppress InvalidArgument false positive
             */
            Process::kill($pid, SIGTERM);
            $this->process = null;
        }

        $this->process = new Process(
            callback: static function (Process $worker) use ($childCommandName): void {
                /**
                 * @psalm-suppress InvalidArgument false positive
                 * @psalm-suppress InvalidCast false positive
                 */
                $worker->exec(PHP_BINARY, [
                    realpath(DM_APP_ROOT.'/../bin/resonance.php'),
                    $childCommandName,
                ]);
            },
            redirect_stdin_and_stdout: false,
        );

        $this->process->start();
        $this->process->wait(false);
    }
}
