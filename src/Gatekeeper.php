<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Psr\Http\Message\ServerRequestInterface;

#[Singleton]
final readonly class Gatekeeper
{
    private GatekeeperRequestContext $gatekeeperRequestContext;

    public function __construct(
        private CrudActionGateAggregate $crudActionGateAggregate,
        AuthenticatedUserStoreAggregate $authenticatedUserSourceAggregate,
        private SiteActionGateAggregate $siteActionGateAggregate,
    ) {
        $this->gatekeeperRequestContext = new GatekeeperRequestContext(
            $crudActionGateAggregate,
            $authenticatedUserSourceAggregate,
            $siteActionGateAggregate,
        );
    }

    public function withRequest(ServerRequestInterface $request): GatekeeperUserContext
    {
        return $this->gatekeeperRequestContext->getUserContext($request);
    }

    public function withUser(?AuthenticatedUser $authenticatedUser): GatekeeperUserContext
    {
        return new GatekeeperUserContext(
            $this->crudActionGateAggregate,
            $this->siteActionGateAggregate,
            $authenticatedUser,
        );
    }
}
