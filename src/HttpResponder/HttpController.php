<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder;

use Closure;
use Distantmagic\Resonance\Attribute\OnParameterResolution;
use Distantmagic\Resonance\HttpControllerDependencies;
use Distantmagic\Resonance\HttpControllerParameterResolution;
use Distantmagic\Resonance\HttpControllerParameterResolutionStatus;
use Distantmagic\Resonance\HttpControllerParameterResolverAggregate;
use Distantmagic\Resonance\HttpControllerReflectionMethod;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpResponder\Error\BadRequest;
use Distantmagic\Resonance\HttpResponder\Error\Forbidden;
use Distantmagic\Resonance\HttpResponder\Error\PageNotFound;
use Distantmagic\Resonance\HttpResponder\Error\ServerError;
use Distantmagic\Resonance\HttpResponderInterface;
use Ds\Map;
use LogicException;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use ReflectionMethod;
use Swoole\Http\Response;

abstract readonly class HttpController extends HttpResponder
{
    public const MAGIC_METHOD_RESPOND = 'fsapokfaspfas';

    private BadRequest $badRequest;
    private Forbidden $forbidden;

    /**
     * @var Map<non-empty-string,Closure>
     */
    private Map $forwardableMethodCallbacks;

    /**
     * @var Map<non-empty-string,HttpControllerReflectionMethod>
     */
    private Map $forwardableMethodReflections;

    private HttpControllerParameterResolverAggregate $httpControllerParameterResolverAggregate;
    private HttpControllerReflectionMethod $invokeReflection;
    private PageNotFound $pageNotFound;
    private ServerError $serverError;

    public function __construct(HttpControllerDependencies $controllerDependencies)
    {
        $this->badRequest = $controllerDependencies->badRequest;
        $this->forbidden = $controllerDependencies->forbidden;
        $this->forwardableMethodCallbacks = new Map();
        $this->forwardableMethodReflections = new Map();
        $this->httpControllerParameterResolverAggregate = $controllerDependencies->httpControllerParameterResolverAggregate;
        $this->pageNotFound = $controllerDependencies->pageNotFound;
        $this->serverError = $controllerDependencies->serverError;

        $reflectionClass = new ReflectionClass($this);

        $this->invokeReflection = $controllerDependencies
            ->httpControllerReflectionMethodCollection
            ->reflectionMethods
            ->get(static::class)
        ;

        foreach ($this->invokeReflection->parameters as $parameter) {
            foreach ($parameter->attributes as $attribute) {
                if ($attribute instanceof OnParameterResolution && !($this->forwardableMethodReflections->hasKey($attribute->forwardTo))) {
                    $forwardableMethodReflection = new ReflectionMethod($this, $attribute->forwardTo);

                    $this->forwardableMethodReflections->put(
                        $attribute->forwardTo,
                        new HttpControllerReflectionMethod($reflectionClass, $forwardableMethodReflection),
                    );
                    $this->forwardableMethodCallbacks->put(
                        $attribute->forwardTo,
                        $forwardableMethodReflection->getClosure($this),
                    );
                }
            }
        }
    }

    final public function respond(ServerRequestInterface $request, Response $response): null|HttpInterceptableInterface|HttpResponderInterface
    {
        if ($this->invokeReflection->parameters->isEmpty()) {
            /**
             * @var null|HttpInterceptableInterface|HttpResponderInterface
             */
            return $this->{self::MAGIC_METHOD_RESPOND}();
        }

        /**
         * @var array <string,mixed>
         */
        $resolvedParameterValues = [];

        foreach ($this->invokeReflection->parameters as $parameter) {
            $parameterResolution = $this->httpControllerParameterResolverAggregate->resolve(
                $request,
                $response,
                $parameter,
            );

            $onParameterResolution = $parameter->onParameterResolution;

            if ($onParameterResolution && $onParameterResolution->status === $parameterResolution->status) {
                return $this->forwardResolvedParameter(
                    $request,
                    $response,
                    $this->forwardableMethodReflections->get($onParameterResolution->forwardTo),
                    $this->forwardableMethodCallbacks->get($onParameterResolution->forwardTo),
                    $parameterResolution,
                );
            }

            switch ($parameterResolution->status) {
                case HttpControllerParameterResolutionStatus::Forbidden:
                    return $this->forbidden;
                case HttpControllerParameterResolutionStatus::NotFound:
                    return $this->pageNotFound;
                case HttpControllerParameterResolutionStatus::MissingUrlParameterValue:
                    return $this->badRequest;
                case HttpControllerParameterResolutionStatus::NoResolver:
                    return $this->serverError;
                case HttpControllerParameterResolutionStatus::Success:
                    /**
                     * @var mixed explicitly mixed for typechecks
                     */
                    $resolvedParameterValues[$parameter->name] = $parameterResolution->value;

                    break;
                case HttpControllerParameterResolutionStatus::ValidationErrors:
                    return $this->badRequest;
                default:
                    throw new LogicException('Unsupported parameter resolution state');
            }
        }

        /**
         * This method is dynamically built and it's checked in the
         * constructor.
         *
         * @var null|HttpInterceptableInterface|HttpResponderInterface
         */
        return $this->{self::MAGIC_METHOD_RESPOND}(...$resolvedParameterValues);
    }

    private function forwardResolvedParameter(
        ServerRequestInterface $request,
        Response $response,
        HttpControllerReflectionMethod $handleValidationErrorsReflection,
        Closure $handleValidationErrorsCallback,
        HttpControllerParameterResolution $httpControllerParameterResolution,
    ): null|HttpInterceptableInterface|HttpResponderInterface {
        /**
         * @var array <string,mixed>
         */
        $resolvedValidationHandlerParameters = [];

        foreach ($handleValidationErrorsReflection->parameters as $parameter) {
            /**
             * @var mixed explicitly mixed for typechecks
             */
            $resolvedValidationHandlerParameters[$parameter->name] = match (true) {
                is_a($parameter->className, HttpControllerParameterResolution::class, true) => $httpControllerParameterResolution,
                is_a($parameter->className, ServerRequestInterface::class, true) => $request,
                is_a($parameter->className, Response::class, true) => $response,
                default => throw new LogicException('ForwardedTo handlers can only use parameters that are already resolved in the handler: '.$parameter->name),
            };
        }

        /**
         * @var null|HttpInterceptableInterface|HttpResponderInterface
         */
        return $handleValidationErrorsCallback(...$resolvedValidationHandlerParameters);
    }
}
