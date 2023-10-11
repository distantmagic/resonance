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

    public function resolve(
        Request $request,
        Response $response,
        HttpControllerParameter $parameter,
    ): HttpControllerParameterResolution {
        $attribute = $parameter->attribute;

        if (!$attribute) {
            throw new LogicException('To use the attribute resolver, attribute must be provided.');
        }

        if ($this->resolvers->hasKey($parameter->attribute::class)) {
            return $this->resolvers->get($parameter->attribute::class)->resolve(
                $request,
                $response,
                $parameter,
                $attribute,
            );
        }

        throw new LogicException(sprintf(
            'There is no resolver registered for attribute: %s',
            $parameter->attribute::class,
        ));
    }
}
