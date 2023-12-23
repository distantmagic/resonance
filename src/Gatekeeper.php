<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Swoole\Http\Request;

#[Singleton]
final readonly class Gatekeeper
{
    private GatekeeperRequestContext $gatekeeperRequestContext;

    public function __construct(
        private CrudActionGateAggregate $crudActionGateAggregate,
        CurrentUserProvider $currentUserProvider,
        private SiteActionGateAggregate $siteActionGateAggregate,
    ) {
        $this->gatekeeperRequestContext = new GatekeeperRequestContext(
            $crudActionGateAggregate,
            $currentUserProvider,
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
            $this->crudActionGateAggregate,
            $this->siteActionGateAggregate,
            $user,
        );
    }
}
