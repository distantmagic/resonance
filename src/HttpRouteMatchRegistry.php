<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use OutOfBoundsException;
use Psr\Http\Message\ServerRequestInterface;
use WeakMap;

#[Singleton]
final readonly class HttpRouteMatchRegistry
{
    /**
     * @var WeakMap<ServerRequestInterface,HttpRouteMatch>
     */
    private WeakMap $matchesMap;

    public function __construct()
    {
        /**
         * @var WeakMap<ServerRequestInterface,HttpRouteMatch>
         */
        $this->matchesMap = new WeakMap();
    }

    public function get(ServerRequestInterface $request): HttpRouteMatch
    {
        if (!$this->matchesMap->offsetExists($request)) {
            throw new OutOfBoundsException('Request does not have a registered route match');
        }

        return $this->matchesMap->offsetGet($request);
    }

    public function getVar(ServerRequestInterface $request, string $varName): string
    {
        return $this->get($request)->routeVars->get($varName);
    }

    public function set(ServerRequestInterface $request, HttpRouteMatch $routeMatch): void
    {
        $this->matchesMap->offsetSet($request, $routeMatch);
    }
}
