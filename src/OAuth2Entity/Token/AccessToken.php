<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\OAuth2Entity\Token;

use Distantmagic\Resonance\OAuth2Entity\Token;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;

class AccessToken extends Token implements AccessTokenEntityInterface
{
    use AccessTokenTrait;
}
