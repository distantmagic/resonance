<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\ServerRequestInterface;

readonly class GatekeeperRequestContext
{
    public function __construct(
        private CrudActionGateAggregate $crudActionGateAggregate,
        private AuthenticatedUserStoreAggregate $authenticatedUserSourceAggregate,
        private SiteActionGateAggregate $siteActionGateAggregate,
    ) {}

    public function getUserContext(ServerRequestInterface $request): GatekeeperUserContext
    {
        return new GatekeeperUserContext(
            $this->crudActionGateAggregate,
            $this->siteActionGateAggregate,
            $this->authenticatedUserSourceAggregate->getAuthenticatedUser($request),
        );
    }
}
