<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder;

use Closure;
use Distantmagic\Resonance\Attribute\CurrentRequest;
use Distantmagic\Resonance\Attribute\CurrentResponse;
use Distantmagic\Resonance\Attribute\ValidationErrors;
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

    /**
     * @var null|Closure(mixed):?HttpResponderInterface
     */
    private ?Closure $handleValidationErrorsCallback;

    private ?HttpControllerReflectionMethod $handleValidationErrorsReflection;
    private HttpControllerParameterResolverAggregate $httpControllerParameterResolverAggregate;
    private PageNotFound $pageNotFound;

    public function __construct(HttpControllerDependencies $controllerDependencies)
    {
        $this->badRequest = $controllerDependencies->badRequest;
        $this->forbidden = $controllerDependencies->forbidden;
        $this->httpControllerParameterResolverAggregate = $controllerDependencies->httpControllerParameterResolverAggregate;
        $this->pageNotFound = $controllerDependencies->pageNotFound;

        $reflectionClass = new ReflectionClass($this);

        /**
         * @var null|Closure(mixed):?HttpResponderInterface
         */
        $handleValidationErrorsCallback = null;
        /**
         * @var null|HttpControllerReflectionMethod
         */
        $handleValidationErrorsReflection = null;

        foreach ($reflectionClass->getMethods() as $validationErrorsReflectionMethod) {
            if (!empty($validationErrorsReflectionMethod->getAttributes(ValidationErrorsHandler::class))) {
                $handleValidationErrorsReflection = new HttpControllerReflectionMethod($validationErrorsReflectionMethod);
                /**
                 * This function is validated in the
                 * HttpControllerReflectionMethod
                 *
                 * @var Closure(mixed):?HttpResponderInterface
                 */
                $handleValidationErrorsCallback = $validationErrorsReflectionMethod->getClosure($this);
            }
        }

        $this->handleValidationErrorsCallback = $handleValidationErrorsCallback;
        $this->handleValidationErrorsReflection = $handleValidationErrorsReflection;

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
         * @var null|Map<string,string>
         */
        $validationErrors = null;

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
                case HttpControllerParameterResolutionStatus::MissingUrlParameterValue:
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

                    if (!$validationErrors) {
                        /**
                         * @var Map<string,string>
                         */
                        $validationErrors = new Map();
                    }

                    $validationErrors->putAll($errors);

                    break;
                default:
                    throw new LogicException('Unsupported parameter resolution state');
            }
        }

        if ($validationErrors) {
            if (!$this->handleValidationErrorsReflection || !$this->handleValidationErrorsCallback) {
                return $this->badRequest;
            }

            return $this->handleValidationErrors(
                $request,
                $response,
                $this->handleValidationErrorsReflection,
                $this->handleValidationErrorsCallback,
                $resolvedParameterValues,
                $validationErrors,
            );
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

    /**
     * @param Closure(mixed):?HttpResponderInterface $handleValidationErrorsCallback
     * @param array <string,mixed>                   $resolvedParameterValues
     * @param Map<string,string>                     $validationErrors
     */
    private function handleValidationErrors(
        Request $request,
        Response $response,
        HttpControllerReflectionMethod $handleValidationErrorsReflection,
        Closure $handleValidationErrorsCallback,
        array $resolvedParameterValues,
        Map $validationErrors,
    ): ?HttpResponderInterface {
        /**
         * @var array <string,mixed>
         */
        $resolvedValidationHandlerParameters = [];

        foreach ($handleValidationErrorsReflection->parameters as $parameter) {
            $attribute = $parameter->attribute;

            if (is_a($attribute, ValidationErrors::class, true)) {
                $resolvedValidationHandlerParameters[$parameter->name] = $validationErrors;
            } elseif (is_a($attribute, CurrentRequest::class, true)) {
                $resolvedValidationHandlerParameters[$parameter->name] = $request;
            } elseif (is_a($attribute, CurrentResponse::class, true)) {
                $resolvedValidationHandlerParameters[$parameter->name] = $response;
            } elseif (array_key_exists($parameter->name, $resolvedParameterValues)) {
                /**
                 * @var mixed explicitly mixed for typechecks
                 */
                $resolvedValidationHandlerParameters[$parameter->name] = $resolvedParameterValues[$parameter->name];
            } else {
                throw new LogicException('ValidationErrorsHandler can only use parameters that are resolved in the handler: '.$parameter->name);
            }
        }

        return $handleValidationErrorsCallback(...$resolvedValidationHandlerParameters);
    }
}
