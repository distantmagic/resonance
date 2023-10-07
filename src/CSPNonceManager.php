<?php

declare(strict_types=1);

namespace Resonance;

use Resonance\Attribute\Singleton;
use Swoole\Http\Request;
use WeakMap;

#[Singleton]
final readonly class CSPNonceManager
{
    /**
     * @var WeakMap<Request, string>
     */
    private WeakMap $nonces;

    public function __construct()
    {
        /**
         * @var WeakMap<Request, string>
         */
        $this->nonces = new WeakMap();
    }

    public function getRequestNonce(Request $request): string
    {
        if ($this->nonces->offsetExists($request)) {
            return $this->nonces->offsetGet($request);
        }

        $nonce = bin2hex(random_bytes(16));

        $this->nonces->offsetSet($request, $nonce);

        return $nonce;
    }
}
