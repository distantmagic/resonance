<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use DateInterval;
use League\OAuth2\Server\Grant\GrantTypeInterface;

readonly class OAuth2Grant
{
    public function __construct(
        public GrantTypeInterface $grantType,
        public ?DateInterval $accessTokenTtl = null,
    ) {}
}
