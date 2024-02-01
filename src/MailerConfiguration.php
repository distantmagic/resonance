<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;

readonly class MailerConfiguration
{
    /**
     * @var Map<non-empty-string,MailerTransportConfiguration>
     */
    public Map $transportConfiguration;

    public function __construct()
    {
        $this->transportConfiguration = new Map();
    }
}
