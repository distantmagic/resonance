<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use LogicException;
use Swoole\Http\Request;
use Swoole\Http\Response;

readonly class HttpRouteParameterBinderAggregate
{
    /**
     * @var Map<class-string,HttpRouteParameterBinderInterface> $httpRouteParameterBinders
     */
    public Map $httpRouteParameterBinders;

    public function __construct()
    {
        $this->httpRouteParameterBinders = new Map();
    }

    /**
     * @template TResult of object
     *
     * @param class-string<TResult> $className
     *
     * @return null|TResult
     */
    public function provide(
        Request $request,
        Response $response,
        string $className,
        string $routeParameterValue,
    ): ?object {
        if (!$this->httpRouteParameterBinders->hasKey($className)) {
            throw new LogicException('There is no parameter binder registered for: '.$className);
        }

        $object = $this->httpRouteParameterBinders->get($className)->provide(
            $request,
            $response,
            $routeParameterValue,
        );

        if (is_null($object)) {
            return null;
        }

        if (!($object instanceof $className)) {
            throw new LogicException('Parameter binder did not return declared value');
        }

        return $object;
    }
}
