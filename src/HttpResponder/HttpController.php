<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder;

use Distantmagic\Resonance\Attribute\ValidationErrorsHandler;
use Distantmagic\Resonance\HttpControllerDependencies;
use Distantmagic\Resonance\HttpControllerParameterResolutionStatus;
use Distantmagic\Resonance\HttpControllerParameterResolverAggregate;
use Distantmagic\Resonance\HttpControllerReflectionMethod;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpResponder\Error\BadRequest;
use Distantmagic\Resonance\HttpResponder\Error\Forbidden;
use Distantmagic\Resonance\HttpResponder\Error\PageNotFound;
use Distantmagic\Resonance\HttpResponderInterface;
use Ds\Map;
use LogicException;
use ReflectionClass;
use ReflectionMethod;
use Swoole\Http\Request;
use Swoole\Http\Response;

abstract readonly class HttpController extends HttpResponder
{
    private BadRequest $badRequest;
    private Forbidden $forbidden;
    private HttpControllerReflectionMethod $handleReflection;
    private HttpControllerParameterResolverAggregate $httpControllerParameterResolverAggregate;
    private PageNotFound $pageNotFound;
    private ?string $validationErrorsHandlerName;

    public function __construct(HttpControllerDependencies $controllerDependencies)
    {
        $this->badRequest = $controllerDependencies->badRequest;
        $this->forbidden = $controllerDependencies->forbidden;
        $this->httpControllerParameterResolverAggregate = $controllerDependencies->httpControllerParameterResolverAggregate;
        $this->pageNotFound = $controllerDependencies->pageNotFound;

        $reflectionClass = new ReflectionClass($this);

        /**
         * @var null|string
         */
        $validationErrorsHandlerName = null;

        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            if (!empty($reflectionMethod->getAttributes(ValidationErrorsHandler::class))) {
                $validationErrorsHandlerName = $reflectionMethod->getName();
            }
        }

        $this->validationErrorsHandlerName = $validationErrorsHandlerName;

        $reflectionMethod = new ReflectionMethod($this, 'handle');
        $this->handleReflection = new HttpControllerReflectionMethod($reflectionMethod);
    }

    public function respond(Request $request, Response $response): ?HttpResponderInterface
    {
        /**
         * @var array <string,mixed>
         */
        $resolvedParameterValues = [];

        /**
         * @var Map<string,string>
         */
        $validationErrors = new Map();

        foreach ($this->handleReflection->parameters as $parameter) {
            $parameterResolution = $this->httpControllerParameterResolverAggregate->resolve(
                $request,
                $response,
                $parameter,
            );

            switch ($parameterResolution->status) {
                case HttpControllerParameterResolutionStatus::Forbidden:
                    return $this->forbidden;
                case HttpControllerParameterResolutionStatus::NotFound:
                    return $this->pageNotFound;
                case HttpControllerParameterResolutionStatus::NotProvided:
                    return $this->badRequest;
                case HttpControllerParameterResolutionStatus::Success:
                    /**
                     * @var mixed explicitly mixed for typechecks
                     */
                    $resolvedParameterValues[$parameter->name] = $parameterResolution->value;

                    break;
                case HttpControllerParameterResolutionStatus::ValidationErrors:
                    /**
                     * Let's assume that types are correct. Otherwise it
                     * would be necessary to iterate over the entire map
                     * during each request.
                     *
                     * @var Map<string,string> $errors
                     */
                    $errors = $parameterResolution->value;

                    $validationErrors->putAll($errors);

                    break;
                default:
                    throw new LogicException('Unsupported parameter resolution state');
            }
        }

        if (!$validationErrors->isEmpty() && $this->validationErrorsHandlerName) {
            /**
             * @var mixed explicitly mixed for typechecks
             */
            $ret = $this->{$this->validationErrorsHandlerName}($request, $response, $validationErrors);

            if (is_null($ret) || ($ret instanceof HttpResponderInterface)) {
                return $ret;
            }

            throw new LogicException('Error handler must return null or '.HttpResponderInterface::class);
        }

        /**
         * This method is dynamically built and it's checked in the
         * constructor.
         *
         * @psalm-suppress UndefinedMethod
         *
         * @var ?HttpResponderInterface
         */
        return $this->handle(...$resolvedParameterValues);
    }
}
