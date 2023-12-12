<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\OAuth2Entity;

use Distantmagic\Resonance\OAuth2Entity;
use League\OAuth2\Server\Entities\TokenInterface;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

abstract class Token extends OAuth2Entity implements TokenInterface
{
    use TokenEntityTrait;
}
