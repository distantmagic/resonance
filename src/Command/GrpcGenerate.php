<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Attribute\RequiresPhpExtension;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\CoroutineCommand;
use Distantmagic\Resonance\CoroutineDriverInterface;
use Distantmagic\Resonance\GrpcConfiguration;
use RuntimeException;
use Swoole\Coroutine\System;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

#[ConsoleCommand(
    name: 'grpc:generate',
    description: 'Generate GRPC stubs'
)]
#[RequiresPhpExtension('grpc')]
#[RequiresPhpExtension('protobuf')]
final class GrpcGenerate extends CoroutineCommand
{
    public function __construct(
        CoroutineDriverInterface $coroutineDriver,
        private readonly Filesystem $filesystem,
        private readonly GrpcConfiguration $grpcConfiguration,
    ) {
        parent::__construct($coroutineDriver);
    }

    protected function configure(): void {}

    protected function executeInCoroutine(InputInterface $input, OutputInterface $output): int
    {
        $scriptName = $_SERVER['SCRIPT_NAME']
            ?? null;

        if (!is_string($scriptName)) {
            throw new RuntimeException('Unable to determine the current script filename');
        }

        foreach ($this->grpcConfiguration->poolConfiguration as $poolConfiguration) {
            $removableFiles = Finder::create()->in($poolConfiguration->outDirectory);

            $this->filesystem->remove($removableFiles);

            $this->mustExec(implode(' ', [
                $poolConfiguration->protocBin,
                sprintf('--grpc_out=generate_server:%s', $poolConfiguration->outDirectory),
                sprintf('--php_out=%s', $poolConfiguration->outDirectory),
                sprintf('--plugin=protoc-gen-grpc=%s', $poolConfiguration->grpcPhpPluginBin),
                sprintf('--proto_path=%s', $poolConfiguration->protosDirectory),
                $poolConfiguration->protoFile,
            ]));
        }

        /**
         * Running that as a separate script resolves lots of issues with
         * PHP internal scripts caching, as they are regenerated on
         * runtime.
         */
        $this->mustExec(implode(' ', [
            PHP_BINARY,
            $scriptName,
            'grpc:rewrite',
        ]));

        return Command::SUCCESS;
    }

    private function mustExec(string $cmd): void
    {
        $result = System::exec($cmd);

        if (!is_array($result) || !array_key_exists('code', $result)) {
            throw new RuntimeException("Unable to determine command's result: ".$cmd);
        }

        if (0 !== $result['code']) {
            throw new RuntimeException('Error while executing: '.$cmd);
        }
    }
}
