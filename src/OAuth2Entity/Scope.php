<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\OAuth2Entity;

use Distantmagic\Resonance\OAuth2Entity;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use ReturnTypeWillChange;

class Scope extends OAuth2Entity implements ScopeEntityInterface
{
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->getIdentifier();
    }
}
