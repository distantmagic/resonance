<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\CoroutineCommand;
use Distantmagic\Resonance\CoroutineDriverInterface;
use Distantmagic\Resonance\JsonSerializer;
use Distantmagic\Resonance\LlamaCppClientInterface;
use Distantmagic\Resonance\LlamaCppInfillRequest;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[ConsoleCommand(
    name: 'llamacpp:infill',
    description: 'Generate code infill'
)]
final class LlamaCppInfill extends CoroutineCommand
{
    public function __construct(
        CoroutineDriverInterface $coroutineDriver,
        private readonly JsonSerializer $jsonSerializer,
        private readonly LlamaCppClientInterface $llamaCppClient,
    ) {
        parent::__construct($coroutineDriver);
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
