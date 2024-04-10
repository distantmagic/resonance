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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use ReflectionMethod;

abstract readonly class HttpController extends HttpResponder
{
    public const MAGIC_METHOD_RESPOND = 'createResponse';

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
    private LoggerInterface $logger;
    private PageNotFound $pageNotFound;
    private ServerError $serverError;

    public function __construct(HttpControllerDependencies $controllerDependencies)
    {
        $this->badRequest = $controllerDependencies->badRequest;
        $this->forbidden = $controllerDependencies->forbidden;
        $this->forwardableMethodCallbacks = new Map();
        $this->forwardableMethodReflections = new Map();
        $this->httpControllerParameterResolverAggregate = $controllerDependencies->httpControllerParameterResolverAggregate;
        $this->logger = $controllerDependencies->logger;
        $this->pageNotFound = $controllerDependencies->pageNotFound;
        $this->serverError = $controllerDependencies->serverError;

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
                        new HttpControllerReflectionMethod($forwardableMethodReflection),
                    );
                    $this->forwardableMethodCallbacks->put(
                        $attribute->forwardTo,
                        $forwardableMethodReflection->getClosure($this),
                    );
                }
            }
        }
    }

    final public function respond(ServerRequestInterface $request, ResponseInterface $response): HttpInterceptableInterface|HttpResponderInterface|ResponseInterface
    {
        if ($this->invokeReflection->parameters->isEmpty()) {
            /**
             * @var HttpInterceptableInterface|HttpResponderInterface|ResponseInterface
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
                    $this->logger->error(sprintf('http_controller_no_resolver("%s", "$%s")', $this::class, $parameter->name));

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
         * @var HttpInterceptableInterface|HttpResponderInterface|ResponseInterface
         */
        return $this->{self::MAGIC_METHOD_RESPOND}(...$resolvedParameterValues);
    }

    private function forwardResolvedParameter(
        ServerRequestInterface $request,
        ResponseInterface $response,
        HttpControllerReflectionMethod $handleValidationErrorsReflection,
        Closure $handleValidationErrorsCallback,
        HttpControllerParameterResolution $httpControllerParameterResolution,
    ): HttpInterceptableInterface|HttpResponderInterface|ResponseInterface {
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
                is_a($parameter->className, ResponseInterface::class, true) => $response,
                default => throw new LogicException('ForwardedTo handlers can only use parameters that are already resolved in the handler: '.$parameter->name),
            };
        }

        /**
         * @var HttpInterceptableInterface|HttpResponderInterface
         */
        return $handleValidationErrorsCallback(...$resolvedValidationHandlerParameters);
    }
}
