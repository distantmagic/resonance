<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Swoole\Http\Request;

#[Singleton]
readonly class CurrentUserProvider
{
    public function __construct(
        private SessionAuthentication $sessionAuthentication,
        private OAuth2Authentication $oAuth2Authentication,
    ) {}

    public function getAuthenticatedUser(Request $request): ?UserInterface
    {
        return $this->sessionAuthentication->getAuthenticatedUser($request);
    }
}
