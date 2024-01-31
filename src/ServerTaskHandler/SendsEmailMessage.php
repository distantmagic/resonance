<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\ServerTaskHandler;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\HandlesServerTask;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\ServerTaskHandler;
use Distantmagic\Resonance\SingletonCollection;
use RuntimeException;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Component\Mime\Message;

/**
 * @template-extends ServerTaskHandler<SendEmailMessage>
 */
#[GrantsFeature(Feature::SwooleTaskServer)]
#[HandlesServerTask(SendEmailMessage::class)]
#[Singleton(collection: SingletonCollection::ServerTaskHandler)]
readonly class SendsEmailMessage extends ServerTaskHandler
{
    public function __construct(
        private DkimSigner $dkimSigner,
        private TransportInterface $transport,
    ) {}

    public function handleServerTask(object $serverTask): void
    {
        $email = $serverTask->getMessage();

        if (!($email instanceof Message)) {
            throw new RuntimeException(sprintf(
                'Expected instanceof "%s", got "%s"',
                Message::class,
                $email::class,
            ));
        }

        $this->transport->send(
            $this->dkimSigner->sign($email),
            $serverTask->getEnvelope(),
        );
    }
}
