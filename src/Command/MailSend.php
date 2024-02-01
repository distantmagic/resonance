<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\MailerRepository;
use RuntimeException;
use Swoole\Event;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mime\Email;

#[ConsoleCommand(
    name: 'mail:send',
    description: 'Send email using the selected transport'
)]
final class MailSend extends Command
{
    public function __construct(
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
            default: 'default',
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

        if (!isset($content, $from, $subject, $to, $transport)) {
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

        Event::wait();

        return Command::SUCCESS;
    }
}
