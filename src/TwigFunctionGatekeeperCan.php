<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Swoole\Http\Request;

#[Singleton]
readonly class TwigFunctionGatekeeperCan
{
    public function __construct(private Gatekeeper $gatekeeper) {}

    public function __invoke(Request $request, SiteActionInterface $siteAction): bool
    {
        return $this->gatekeeper->withRequest($request)->can($siteAction);
    }
}
