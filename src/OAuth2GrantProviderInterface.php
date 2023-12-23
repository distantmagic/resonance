<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use DateInterval;
use League\OAuth2\Server\Grant\GrantTypeInterface;

interface OAuth2GrantProviderInterface
{
    public function getAccessTokenTTL(): DateInterval;

    public function provideGrant(): GrantTypeInterface;
}
