<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\OAuth2Entity;

use Distantmagic\Resonance\OAuth2Entity;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;

class RefreshToken extends OAuth2Entity implements RefreshTokenEntityInterface
{
    use RefreshTokenTrait;
}
