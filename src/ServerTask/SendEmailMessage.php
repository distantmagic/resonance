<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\ServerTask;

use Symfony\Component\Mime\Message;

readonly class SendEmailMessage
{
    /**
     * @param non-empty-string $transportName
     */
    public function __construct(
        public string $transportName,
        public Message $message,
    ) {}
}
