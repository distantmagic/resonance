<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use League\OAuth2\Server\Entities\ScopeEntityInterface;

interface OAuth2ScopeInterface extends ScopeEntityInterface
{
    public function getIdentifier(): string;
}
