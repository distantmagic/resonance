<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\CoroutineCommand;
use Distantmagic\Resonance\JsonSerializer;
use Distantmagic\Resonance\LlamaCppClient;
use Distantmagic\Resonance\LlamaCppInfillRequest;
use Distantmagic\Resonance\SwooleConfiguration;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[ConsoleCommand(
    name: 'llamacpp:infill',
    description: 'Generate code infill'
)]
final class LlamaCppInfill extends CoroutineCommand
{
    public function __construct(
        private readonly JsonSerializer $jsonSerializer,
        private readonly LlamaCppClient $llamaCppClient,
        SwooleConfiguration $swooleConfiguration,
    ) {
        parent::__construct($swooleConfiguration);
    }

    protected function executeInCoroutine(InputInterface $input, OutputInterface $output): int
    {
        $request = new LlamaCppInfillRequest(
            before: '<?php // hello world',
            after: '?>',
        );

        foreach ($this->llamaCppClient->generateInfill($request) as $token) {
            $output->write((string) $token);
        }

        return Command::SUCCESS;
    }
}
