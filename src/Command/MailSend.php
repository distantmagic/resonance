<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Attribute\WantsFeature;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\CoroutineDriverInterface;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\MailerRepository;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mime\Email;

#[ConsoleCommand(
    name: 'mail:send',
    description: 'Send email using the selected transport'
)]
#[WantsFeature(Feature::Mailer)]
final class MailSend extends Command
{
    public function __construct(
        private readonly CoroutineDriverInterface $coroutineDriver,
        private readonly MailerRepository $mailerRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            name: 'from',
            mode: InputOption::VALUE_REQUIRED,
            description: 'From who the email will be delivered',
        );
        $this->addOption(
            name: 'to',
            mode: InputOption::VALUE_REQUIRED,
            description: 'To whom the email will be delivered',
        );
        $this->addOption(
            name: 'subject',
            mode: InputOption::VALUE_REQUIRED,
            description: 'Email subject',
        );
        $this->addOption(
            name: 'transport',
            mode: InputOption::VALUE_REQUIRED,
            suggestedValues: $this->mailerRepository->mailer->keys()->toArray(),
        );
        $this->addArgument(
            name: 'content',
            mode: InputArgument::REQUIRED,
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $content = $input->getArgument('content');
        $from = $input->getOption('from');
        $subject = $input->getOption('subject');
        $to = $input->getOption('to');
        $transport = $input->getOption('transport');

        if (
            !is_string($content)
            || !is_string($from)
            || !is_string($subject)
            || !is_string($to)
            || !is_string($transport)
            || empty($content)
            || empty($from)
            || empty($subject)
            || empty($to)
            || empty($transport)
        ) {
            throw new RuntimeException('You need to provide all the options and arguments');
        }

        $email = (new Email())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->text($content)
            ->html($content)
        ;

        $this->mailerRepository->mailer->get($transport)->enqueue($email);
        $this->coroutineDriver->wait();

        return Command::SUCCESS;
    }
}
