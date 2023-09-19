<?php

declare(strict_types=1);

namespace Resonance;

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

    public function withUser(?UserInterface $user): GatekeeperUserContext
    {
        return new GatekeeperUserContext(
            $this->siteActionGateAggregate,
            $user,
        );
    }
}
