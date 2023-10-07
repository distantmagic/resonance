<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use DomainException;
use Ds\Map;

readonly class SiteActionGateAggregate
{
    /**
     * @var Map<SiteActionInterface,SiteActionGate>
     */
    public Map $siteActionGates;

    public function __construct()
    {
        /**
         * @var Map<SiteActionInterface,SiteActionGate>
         */
        $this->siteActionGates = new Map();
    }

    public function selectSiteActionGate(SiteActionInterface $action): SiteActionGate
    {
        if (!$this->siteActionGates->hasKey($action)) {
            throw new DomainException('Unsupported gatekeeper action: '.$action->getName());
        }

        return $this->siteActionGates->get($action);
    }
}
