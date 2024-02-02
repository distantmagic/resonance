<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Event;

use Distantmagic\Resonance\Event;

/**
 * @psalm-suppress PossiblyUnusedProperty used in listeners
 */
final readonly class MailBounced extends Event
{
    /**
     * @param non-empty-string      $recipient
     * @param null|non-empty-string $diagnosticCode
     * @param null|non-empty-string $notification
     * @param null|non-empty-string $sender
     * @param null|non-empty-string $status
     */
    public function __construct(
        public string $recipient,
        public ?string $diagnosticCode,
        public ?string $notification,
        public ?string $sender,
        public ?string $status,
    ) {}
}
