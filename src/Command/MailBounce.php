<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\CoroutineCommand;
use Distantmagic\Resonance\Event\MailBounced;
use Distantmagic\Resonance\EventDispatcherInterface;
use Distantmagic\Resonance\Mailer;
use Distantmagic\Resonance\MailerRepository;
use Distantmagic\Resonance\SwooleConfiguration;
use Generator;
use http\Header;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @psalm-type PPartData = array{
 *     content-description?: non-empty-string,
 *     ending-pos-body: int,
 *     starting-pos-body: int,
 * }
 */
#[ConsoleCommand(
    name: 'mail:bounce',
    description: 'Handles email bounces (requires mailparse)'
)]
final class MailBounce extends CoroutineCommand
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger,
        private readonly MailerRepository $mailerRepository,
        SwooleConfiguration $swooleConfiguration,
    ) {
        parent::__construct($swooleConfiguration);
    }

    protected function executeInCoroutine(InputInterface $input, OutputInterface $output): int
    {
        if (!extension_loaded('mailparse')) {
            throw new RuntimeException('You need to install "mailparse" extension');
        }

        $content = stream_get_contents(STDIN);

        if (false === $content || empty($content)) {
            throw new RuntimeException('Expected email contents in STDIN');
        }

        /**
         * @var array{
         *     diagnostic-code?: non-empty-string,
         *     original-recipient?: non-empty-string,
         *     status?: non-empty-string,
         *     x-postfix-sender?: non-empty-string,
         * } $ret
         */
        $ret = [];

        foreach ($this->parseMessageContent($content) as $partData => $partBody) {
            if (!isset($partData['content-description'])) {
                continue;
            }

            if ('Notification' === $partData['content-description']) {
                $ret['notification'] = $partBody;
            }

            if ('Delivery report' === $partData['content-description']) {
                foreach ($this->parseHeaders($partBody) as $name => $value) {
                    switch ($name) {
                        case 'Original-Recipient':
                        case 'X-Postfix-Sender':
                            $ret[strtolower($name)] = $this->parseEmail($value);

                            break;
                        case 'Diagnostic-Code':
                        case 'Status':
                            $ret[strtolower($name)] = $value;

                            break;
                    }
                }
            }
        }

        /**
         * That alone is enough to notify about the bounce.
         */
        if (isset($ret['original-recipient']) && !empty($ret['original-recipient'])) {
            $this->eventDispatcher->dispatch(new MailBounced(
                recipient: $ret['original-recipient'],
                diagnosticCode: empty($ret['diagnostic-code']) ? null : $ret['diagnostic-code'],
                notification: empty($ret['notification']) ? null : $ret['notification'],
                sender: empty($ret['x-postfix-sender']) ? null : $ret['x-postfix-sender'],
                status: empty($ret['status']) ? null : $ret['status'],
            ));
        }

        return Command::SUCCESS;
    }

    private function getMailer(InputInterface $input): Mailer
    {
        $transportName = $input->getOption('transport');

        if (!is_string($transportName) || empty($transportName)) {
            throw new RuntimeException('You need to provide all the options and arguments');
        }

        $transport = $this->mailerRepository->mailer->get($transportName, null);

        if (!$transport) {
            throw new RuntimeException(sprintf('Transport is not configured: "%s"', $transportName));
        }

        return $transport;
    }

    /**
     * @param non-empty-string $email
     *
     * @return non-empty-string
     */
    private function parseEmail(string $email): string
    {
        if (str_starts_with($email, 'rfc822;')) {
            $trimmed = trim(substr($email, 7));

            if (empty($trimmed)) {
                throw new RuntimeException(sprintf('Unable to parse an email: "%s"', $email));
            }

            return $trimmed;
        }

        return $email;
    }

    /**
     * @return Generator<non-empty-string,non-empty-string>
     */
    private function parseHeaders(string $content): Generator
    {
        $headers = explode("\n", $content);

        foreach ($headers as $header) {
            /**
             * @var array<non-empty-string,non-empty-string>|false $parsed
             */
            $parsed = Header::parse($header);

            if (false === $parsed) {
                continue;
            }

            foreach ($parsed as $name => $value) {
                yield $name => $value;
            }
        }
    }

    /**
     * @return Generator<PPartData,string>
     */
    private function parseMessageContent(string $content): Generator
    {
        $message = mailparse_msg_create();

        try {
            if (!mailparse_msg_parse($message, $content)) {
                throw new RuntimeException('Unable to parse the email message');
            }

            $structure = mailparse_msg_get_structure($message);

            /**
             * @var string $partId
             */
            foreach ($structure as $partId) {
                $part = mailparse_msg_get_part($message, $partId);

                /**
                 * @var PPartData $partData
                 */
                $partData = mailparse_msg_get_part_data($part);

                yield $partData => trim(mb_substr(
                    $content,
                    $partData['starting-pos-body'],
                    $partData['ending-pos-body'] - $partData['starting-pos-body']
                ));
            }
        } finally {
            if (!mailparse_msg_free($message)) {
                throw new RuntimeException('Unable to free the message resource');
            }
        }
    }
}
