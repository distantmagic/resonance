<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use LogicException;
use Swoole\Http\Request;
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

    /**
     * @param class-string $parameterClass
     */
    public function resolve(
        Request $request,
        Response $response,
        Attribute $responderAttribute,
        string $parameterClass,
    ): mixed {
        if ($this->resolvers->hasKey($responderAttribute::class)) {
            return $this->resolvers->get($responderAttribute::class)->resolve(
                $request,
                $response,
                $responderAttribute,
                $parameterClass,
            );
        }

        throw new LogicException(sprintf(
            'There is no resolver registered for attribute: %s',
            $responderAttribute::class,
        ));
    }
}
