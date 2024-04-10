<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder;

use Closure;
use Distantmagic\Resonance\Attribute\OnParameterResolution;
use Distantmagic\Resonance\HttpControllerDependencies;
use Distantmagic\Resonance\HttpControllerReflectionMethod;
use Distantmagic\Resonance\HttpControllerRequestHandler;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpResponderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

abstract readonly class HttpController extends HttpResponder
{
    public const MAGIC_METHOD_RESPOND = 'createResponse';

    private HttpControllerRequestHandler $httpControllerRequestHandler;

    public function __construct(HttpControllerDependencies $controllerDependencies)
    {
        $reflectionClass = new ReflectionClass($this);
        $reflectionFunction = $reflectionClass->getMethod(self::MAGIC_METHOD_RESPOND);

        $responderClosure = $reflectionFunction->getClosure($this);

        if (!($responderClosure instanceof Closure)) {
            throw new RuntimeException('Unable to create controller responder closure');
        }

        $this->httpControllerRequestHandler = new HttpControllerRequestHandler(
            controllerDependencies: $controllerDependencies,
            responderClosure: $responderClosure,
            reflectionFunction: $reflectionFunction,
        );

        foreach ($this->httpControllerRequestHandler->invokeReflection->parameters as $parameter) {
            foreach ($parameter->attributes as $attribute) {
                if ($attribute instanceof OnParameterResolution && !($this->httpControllerRequestHandler->forwardableMethodReflections->hasKey($attribute->forwardTo))) {
                    $forwardableMethodReflection = new ReflectionMethod($this, $attribute->forwardTo);

                    $this->httpControllerRequestHandler->forwardableMethodReflections->put(
                        $attribute->forwardTo,
                        new HttpControllerReflectionMethod($forwardableMethodReflection),
                    );
                    $this->httpControllerRequestHandler->forwardableMethodCallbacks->put(
                        $attribute->forwardTo,
                        $forwardableMethodReflection->getClosure($this),
                    );
                }
            }
        }
    }

    final public function respond(ServerRequestInterface $request, ResponseInterface $response): HttpInterceptableInterface|HttpResponderInterface|ResponseInterface
    {
        return $this->httpControllerRequestHandler->respond($request, $response);
    }
}
