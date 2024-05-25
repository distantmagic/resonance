<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\CoroutineCommand;
use Distantmagic\Resonance\CoroutineDriverInterface;
use Distantmagic\Resonance\LlamaCppClientInterface;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class LlamaCppGenerate extends CoroutineCommand
{
    /**
     * @param non-empty-string $prompt
     */
    abstract protected function executeLlamaCppCommand(InputInterface $input, OutputInterface $output, string $prompt): int;

    public function __construct(
        CoroutineDriverInterface $coroutineDriver,
        protected LlamaCppClientInterface $llamaCppClient,
    ) {
        parent::__construct($coroutineDriver);
    }

    protected function configure(): void
    {
        $this->addArgument(
            name: 'prompt',
            mode: InputArgument::OPTIONAL,
            default: 'How to make a cat happy? Be brief, respond in 1 sentence.',
        );
    }

    protected function executeInCoroutine(InputInterface $input, OutputInterface $output): int
    {
        /**
         * @var string $prompt
         */
        $prompt = $input->getArgument('prompt');

        if (empty($prompt)) {
            throw new RuntimeException('Prompt cannot be empty');
        }

        return $this->executeLlamaCppCommand($input, $output, $prompt);
    }
}
