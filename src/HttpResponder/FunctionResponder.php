<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder;

use Distantmagic\Resonance\HttpControllerDependencies;
use Distantmagic\Resonance\HttpControllerRequestHandler;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpResponderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionFunction;

/**
 * @psalm-suppress MixedInferredReturnType
 * @psalm-suppress MixedReturnStatement
 */
readonly class FunctionResponder extends HttpResponder
{
    private HttpControllerRequestHandler $httpControllerRequestHandler;

    public function __construct(
        HttpControllerDependencies $controllerDependencies,
        ReflectionFunction $responderFunctionReflection
    ) {
        $this->httpControllerRequestHandler = new HttpControllerRequestHandler(
            controllerDependencies: $controllerDependencies,
            responderClosure: $responderFunctionReflection->getClosure(),
            reflectionFunction: $responderFunctionReflection,
        );
    }

    public function respond(ServerRequestInterface $request, ResponseInterface $response): HttpInterceptableInterface|HttpResponderInterface|ResponseInterface
    {
        return $this->httpControllerRequestHandler->respond($request, $response);
    }
}
