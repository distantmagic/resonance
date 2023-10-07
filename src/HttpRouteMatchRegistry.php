<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use OutOfBoundsException;
use Distantmagic\Resonance\Attribute\Singleton;
use Swoole\Http\Request;
use WeakMap;

#[Singleton]
final readonly class HttpRouteMatchRegistry
{
    /**
     * @var WeakMap<Request, HttpRouteMatch>
     */
    private WeakMap $matchesMap;

    public function __construct()
    {
        /**
         * @var WeakMap<Request, HttpRouteMatch>
         */
        $this->matchesMap = new WeakMap();
    }

    public function get(Request $request): HttpRouteMatch
    {
        if (!$this->matchesMap->offsetExists($request)) {
            throw new OutOfBoundsException('Request does not have a registered route match');
        }

        return $this->matchesMap->offsetGet($request);
    }

    public function getVar(Request $request, string $varName): string
    {
        return $this->get($request)->routeVars->get($varName);
    }

    public function set(Request $request, HttpRouteMatch $routeMatch): void
    {
        $this->matchesMap->offsetSet($request, $routeMatch);
    }
}
