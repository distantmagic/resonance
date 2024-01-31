<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\Singleton;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[GrantsFeature(Feature::SwooleTaskServer)]
#[Singleton]
readonly class SwooleTaskServerMessageBus implements MessageBusInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private SwooleTaskServerMessageBroker $swooleTaskServerMessageBroker,
    ) {}

    public function dispatch(object $message, array $stamps = []): Envelope
    {
        $this->logger->debug(sprintf(
            'task_server_message_bus_dispatch(%s)',
            $message::class,
        ));

        $envelope = Envelope::wrap($message, $stamps);

        $this->swooleTaskServerMessageBroker->dispatch($envelope);

        return $envelope;
    }
}
