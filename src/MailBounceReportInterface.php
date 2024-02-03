<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Symfony\Component\Mime\Address;

interface MailBounceReportInterface
{
    /**
     * @return null|non-empty-string
     */
    public function getDiagnosticCode(): ?string;

    /**
     * @return null|non-empty-string
     */
    public function getNotification(): ?string;

    public function getRecipient(): Address;

    public function getSender(): ?Address;

    /**
     * @return null|non-empty-string
     */
    public function getStatus(): ?string;
}
