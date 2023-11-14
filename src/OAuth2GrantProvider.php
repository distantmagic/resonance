<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use DateInterval;

abstract readonly class OAuth2GrantProvider implements OAuth2GrantProviderInterface
{
    public function getAccessTokenTTL(): DateInterval
    {
        return new DateInterval('PT1H');
    }
}
