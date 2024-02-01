<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\ServerTask\SendEmailMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Component\Mime\Message;

readonly class Mailer
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(
        private ?DkimSigner $dkimSigner,
        private ?MessageBusInterface $messageBus,
        private string $name,
        private TransportInterface $transport,
    ) {}

    public function enqueue(Message $message): void
    {
        if ($this->messageBus) {
            $this->messageBus->dispatch(new SendEmailMessage($this->name, $message));
        } else {
            $this->send($message);
        }
    }

    public function send(Message $message): void
    {
        $this->transport->send(
            $this->dkimSigner ? $this->dkimSigner->sign($message) : $message
        );
    }
}
