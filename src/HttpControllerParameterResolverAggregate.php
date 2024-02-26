<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use LogicException;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Response;

readonly class HttpControllerParameterResolverAggregate
{
    /**
     * @var Map<class-string<Attribute>,HttpControllerParameterResolverInterface>
     */
    public Map $resolvers;

    public function __construct()
    {
        $this->resolvers = new Map();
    }

    public function resolve(
        ServerRequestInterface $request,
        Response $response,
        HttpControllerParameter $parameter,
    ): HttpControllerParameterResolution {
        /**
         * @var null|HttpControllerParameterResolution $resolved
         */
        $resolved = null;

        foreach ($parameter->attributes as $attribute) {
            if ($this->resolvers->hasKey($attribute::class)) {
                if (!is_null($resolved)) {
                    throw new LogicException('Ambiguous parameter resolution. You can only use one resolving attribute.');
                }

                $resolved = $this->resolvers->get($attribute::class)->resolve(
                    $request,
                    $response,
                    $parameter,
                    $attribute,
                );
            }
        }

        if ($resolved) {
            return $resolved;
        }

        return new HttpControllerParameterResolution(
            status: HttpControllerParameterResolutionStatus::NoResolver,
        );
    }
}
