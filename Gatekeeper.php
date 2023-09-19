<?php

declare(strict_types=1);

namespace Resonance;

use App\DatabaseEntity\User;
use Resonance\Attribute\Singleton;
use Swoole\Http\Request;

#[Singleton]
final readonly class Gatekeeper
{
    private GatekeeperRequestContext $gatekeeperRequestContext;

    public function __construct(
        SessionAuthentication $sessionAuthentication,
        private SiteActionGateAggregate $siteActionGateAggregate,
    ) {
        $this->gatekeeperRequestContext = new GatekeeperRequestContext(
            $sessionAuthentication,
            $siteActionGateAggregate,
        );
    }

    public function withRequest(Request $request): GatekeeperUserContext
    {
        return $this->gatekeeperRequestContext->getUserContext($request);
    }

    public function withUser(?User $user): GatekeeperUserContext
    {
        return new GatekeeperUserContext(
            $this->siteActionGateAggregate,
            $user,
        );
    }
}
