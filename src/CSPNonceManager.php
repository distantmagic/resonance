<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Psr\Http\Message\ServerRequestInterface;
use WeakMap;

#[Singleton]
final readonly class CSPNonceManager
{
    /**
     * @var WeakMap<ServerRequestInterface,string>
     */
    private WeakMap $nonces;

    public function __construct()
    {
        /**
         * @var WeakMap<ServerRequestInterface,string>
         */
        $this->nonces = new WeakMap();
    }

    public function getRequestNonce(ServerRequestInterface $request): string
    {
        if ($this->nonces->offsetExists($request)) {
            return $this->nonces->offsetGet($request);
        }

        $nonce = bin2hex(random_bytes(16));

        $this->nonces->offsetSet($request, $nonce);

        return $nonce;
    }
}
