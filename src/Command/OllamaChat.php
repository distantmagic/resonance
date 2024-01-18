<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\CoroutineCommand;
use Distantmagic\Resonance\OllamaChatSession;
use Distantmagic\Resonance\OllamaClient;
use Distantmagic\Resonance\SwooleConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

#[ConsoleCommand(
    name: 'ollama:chat',
    description: 'Chat with LLM model through Ollama'
)]
final class OllamaChat extends CoroutineCommand
{
    public function __construct(
        protected OllamaClient $ollamaClient,
        SwooleConfiguration $swooleConfiguration,
    ) {
        parent::__construct($swooleConfiguration);
    }

    protected function configure(): void
    {
        $this->addOption(
            default: 'mistral',
            mode: InputOption::VALUE_REQUIRED,
            name: 'model',
        );
    }

    protected function executeInCoroutine(InputInterface $input, OutputInterface $output): int
    {
        /**
         * @var string $model
         */
        $model = $input->getOption('model');

        /**
         * @var QuestionHelper $helper
         */
        $helper = $this->getHelper('question');
        $userInputQuestion = new Question('> ');

        $chatSession = new OllamaChatSession(
            model: $model,
            ollamaClient: $this->ollamaClient,
        );

        while (true) {
            $userMessageContent = $helper->ask($input, $output, $userInputQuestion);

            foreach ($chatSession->respond($userMessageContent) as $value) {
                $output->write((string) $value);
            }

            $output->writeln('');
        }

        return Command::SUCCESS;
    }
}
