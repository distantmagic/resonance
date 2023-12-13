<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use League\OAuth2\Server\Entities\ClientEntityInterface;

interface OAuth2GrantAwareClient extends ClientEntityInterface
{
    public function isGrantTypeAccepted(string $grantType): bool;

    public function isGrantTypeRequired(): bool;
}
