<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionFunction;

readonly class HttpResponderFunction implements HttpResponderInterface
{
    private Closure $responderFunction;

    public function __construct(ReflectionFunction $responderFunctionReflection)
    {
        $this->responderFunction = $responderFunctionReflection->getClosure();
    }

    public function respond(ServerRequestInterface $request, ResponseInterface $response): HttpInterceptableInterface|HttpResponderInterface|ResponseInterface
    {
        return ($this->responderFunction)($request, $response);
    }
}
