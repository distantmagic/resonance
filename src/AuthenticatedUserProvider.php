<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Swoole\Http\Request;

#[Singleton]
readonly class AuthenticatedUserProvider
{
    public function __construct(
        private SessionAuthentication $sessionAuthentication,
        private ?OAuth2ClaimReader $oAuth2ClaimReader = null,
    ) {}

    public function getAuthenticatedUser(Request $request): ?AuthenticatedUser
    {
        $sessionAuthenticatedUser = $this->sessionAuthentication->getAuthenticatedUser($request);

        if ($sessionAuthenticatedUser) {
            return new AuthenticatedUser(
                AuthenticatedUserSource::Session,
                $sessionAuthenticatedUser,
            );
        }

        if (!$this->oAuth2ClaimReader?->hasClaim($request)) {
            return null;
        }

        return new AuthenticatedUser(
            AuthenticatedUserSource::OAuth2,
            $this->oAuth2ClaimReader->readClaim($request)->user,
        );
    }
}
