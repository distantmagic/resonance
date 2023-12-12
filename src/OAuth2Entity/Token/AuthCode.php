<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\OAuth2Entity\Token;

use Distantmagic\Resonance\OAuth2Entity\Token;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\Traits\AuthCodeTrait;

class AuthCode extends Token implements AuthCodeEntityInterface
{
    use AuthCodeTrait;
}
