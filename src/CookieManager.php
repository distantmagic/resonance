<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Ds\Set;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Cookie;
use WeakMap;

#[Singleton]
readonly class CookieManager
{
    /**
     * @var WeakMap<ServerRequestInterface,Set<Cookie>>
     */
    private WeakMap $cookies;

    public function __construct()
    {
        /**
         * @var WeakMap<ServerRequestInterface,Set<Cookie>>
         */
        $this->cookies = new WeakMap();
    }

    /**
     * @return Set<Cookie>
     */
    public function getCookieJar(ServerRequestInterface $request): Set
    {
        if (!$this->cookies->offsetExists($request)) {
            $this->cookies->offsetSet($request, new Set());
        }

        return $this->cookies->offsetGet($request);
    }
}
