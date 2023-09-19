<?php

declare(strict_types=1);

namespace Resonance;

use Swoole\Http\Request;

readonly class GatekeeperRequestContext
{
    public function __construct(
        private SessionAuthentication $sessionAuthentication,
        private SiteActionGateAggregate $siteActionGateAggregate,
    ) {}

    public function getUserContext(Request $request): GatekeeperUserContext
    {
        return new GatekeeperUserContext(
            $this->siteActionGateAggregate,
            $this->sessionAuthentication->authenticatedUser($request),
        );
    }
}
