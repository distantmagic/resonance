<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Swoole\Http\Request;

#[Singleton]
readonly class TwigFunctionCSPNonce
{
    public function __construct(private CSPNonceManager $cspNonceManager) {}

    public function __invoke(Request $request): string
    {
        return $this->cspNonceManager->getRequestNonce($request);
    }
}
