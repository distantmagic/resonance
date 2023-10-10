<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder;

use Closure;
use Distantmagic\Resonance\HttpControllerDependencies;
use Distantmagic\Resonance\HttpControllerParameterResolutionStatus;
use Distantmagic\Resonance\HttpControllerParameterResolverAggregate;
use Distantmagic\Resonance\HttpControllerReflectionMethod;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpResponder\Error\BadRequest;
use Distantmagic\Resonance\HttpResponder\Error\Forbidden;
use Distantmagic\Resonance\HttpResponder\Error\PageNotFound;
use Distantmagic\Resonance\HttpResponderInterface;
use ReflectionMethod;
use Swoole\Http\Request;
use Swoole\Http\Response;

abstract readonly class HttpController extends HttpResponder
{
    private BadRequest $badRequest;
    private Forbidden $forbidden;
    private HttpControllerReflectionMethod $handleReflection;

    /**
     * @var Closure(mixed): ?HttpResponderInterface
     */
    private Closure $handleReflectionCallback;

    private HttpControllerParameterResolverAggregate $httpControllerParameterResolverAggregate;
    private PageNotFound $pageNotFound;

    public function __construct(HttpControllerDependencies $controllerDependencies)
    {
        $this->badRequest = $controllerDependencies->badRequest;
        $this->forbidden = $controllerDependencies->forbidden;
        $this->httpControllerParameterResolverAggregate = $controllerDependencies->httpControllerParameterResolverAggregate;
        $this->pageNotFound = $controllerDependencies->pageNotFound;

        $reflectionMethod = new ReflectionMethod($this, 'handle');

        $this->handleReflection = new HttpControllerReflectionMethod($reflectionMethod);

        /**
         * @var Closure(mixed): ?HttpResponderInterface
         */
        $this->handleReflectionCallback = $reflectionMethod->getClosure($this);
    }

    public function respond(Request $request, Response $response): ?HttpResponderInterface
    {
        /**
         * @var array <string,mixed>
         */
        $resolvedParameterValues = [];

        foreach ($this->handleReflection->parameters as $parameter) {
            /**
             * @var mixed explicitly mixed for typechecks
             */
            $parameterValue = $this->httpControllerParameterResolverAggregate->resolve(
                $request,
                $response,
                $parameter,
            );

            switch ($parameterValue) {
                case HttpControllerParameterResolutionStatus::Forbidden:
                    return $this->forbidden;
                case HttpControllerParameterResolutionStatus::NotFound:
                    return $this->pageNotFound;
                case HttpControllerParameterResolutionStatus::NotProvided:
                    return $this->badRequest;
                default:
                    /**
                     * @var mixed explicitly mixed for typechecks
                     */
                    $resolvedParameterValues[$parameter->name] = $parameterValue;

                    break;
            }
        }

        return ($this->handleReflectionCallback)(...$resolvedParameterValues);
    }
}
