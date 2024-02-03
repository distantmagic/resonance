<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Symfony\Component\Mime\Address;

/**
 * @psalm-suppress PossiblyUnusedProperty used in listeners
 */
final readonly class PostfixBounceReport extends Event implements MailBounceReportInterface
{
    /**
     * @param null|non-empty-string $diagnosticCode
     * @param null|non-empty-string $notification
     * @param null|non-empty-string $status
     */
    public function __construct(
        private Address $recipient,
        private ?string $diagnosticCode,
        private ?string $notification,
        private ?Address $sender,
        private ?string $status,
    ) {}

    /**
     * @return null|non-empty-string
     */
    public function getDiagnosticCode(): ?string
    {
        return $this->diagnosticCode;
    }

    /**
     * @return null|non-empty-string
     */
    public function getNotification(): ?string
    {
        return $this->notification;
    }

    public function getRecipient(): Address
    {
        return $this->recipient;
    }

    public function getSender(): ?Address
    {
        return $this->sender;
    }

    /**
     * @return null|non-empty-string
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }
}
