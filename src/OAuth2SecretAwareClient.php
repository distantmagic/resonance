<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use League\OAuth2\Server\Entities\ClientEntityInterface;

interface OAuth2SecretAwareClient extends ClientEntityInterface
{
    public function isSecretRequired(): bool;

    public function isSecretValid(string $secret): bool;
}
