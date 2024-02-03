<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Attribute\WantsFeature;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\CoroutineCommand;
use Distantmagic\Resonance\Event\MailBounced;
use Distantmagic\Resonance\EventDispatcherInterface;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\PostfixBounceAnalyzer;
use Distantmagic\Resonance\SwooleConfiguration;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[ConsoleCommand(
    name: 'postfix:bounce',
    description: 'Handles email bounces (requires mailparse)'
)]
#[WantsFeature(Feature::Postfix)]
final class PostfixBounce extends CoroutineCommand
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger,
        private readonly PostfixBounceAnalyzer $postfixBounceAnalyzer,
        SwooleConfiguration $swooleConfiguration,
    ) {
        parent::__construct($swooleConfiguration);
    }

    protected function executeInCoroutine(InputInterface $input, OutputInterface $output): int
    {
        if (!extension_loaded('mailparse') || !extension_loaded('http')) {
            throw new RuntimeException('You need to install "http" and "mailparse" extensions');
        }

        $content = stream_get_contents(STDIN);

        if (false === $content || empty($content)) {
            throw new RuntimeException('Expected email contents in STDIN');
        }

        $postfixBounceReport = $this->postfixBounceAnalyzer->extractReport($content);

        if ($postfixBounceReport) {
            $this->eventDispatcher->dispatch(new MailBounced($postfixBounceReport));
        }

        return Command::SUCCESS;
    }
}
