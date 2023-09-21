<?php

declare(strict_types=1);

namespace Resonance;

use Ds\Map;
use Ds\Set;

readonly class SiteActionSubjectAggregate
{
    /**
     * @var Map<HttpResponderInterface,Set<SiteActionInterface>>
     */
    private Map $responders;

    public function __construct()
    {
        $this->responders = new Map();
    }

    /**
     * @return Set<SiteActionInterface>
     */
    public function getSiteActions(HttpResponderInterface $responder): Set
    {
        if (!$this->responders->hasKey($responder)) {
            $this->responders->put($responder, new Set());
        }

        return $this->responders->get($responder);
    }

    public function registerSiteAction(
        HttpResponderInterface $responder,
        SiteActionInterface $siteAction,
    ): void {
        $siteActions = $this->getSiteActions($responder);
        $siteActions->add($siteAction);
    }
}
