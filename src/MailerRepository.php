<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;

readonly class MailerRepository
{
    /**
     * @var Map<non-empty-string,Mailer>
     */
    public Map $mailer;

    public function __construct()
    {
        $this->mailer = new Map();
    }
}
