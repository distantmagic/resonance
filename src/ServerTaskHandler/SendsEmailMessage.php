<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\ServerTaskHandler;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\HandlesServerTask;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\MailerRepository;
use Distantmagic\Resonance\ServerTask\SendEmailMessage;
use Distantmagic\Resonance\ServerTaskHandler;
use Distantmagic\Resonance\SingletonCollection;

/**
 * @template-extends ServerTaskHandler<SendEmailMessage>
 */
#[GrantsFeature(Feature::SwooleTaskServer)]
#[GrantsFeature(Feature::Mailer)]
#[HandlesServerTask(SendEmailMessage::class)]
#[Singleton(collection: SingletonCollection::ServerTaskHandler)]
readonly class SendsEmailMessage extends ServerTaskHandler
{
    public function __construct(
        private MailerRepository $mailerRepository
    ) {}

    public function handleServerTask(object $serverTask): void
    {
        $this
            ->mailerRepository
            ->mailer
            ->get($serverTask->transportName)
            ->send($serverTask->message)
        ;
    }
}
