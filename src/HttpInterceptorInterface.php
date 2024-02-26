<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @template TClass
 */
interface HttpInterceptorInterface
{
    /**
     * @param TClass $intercepted
     */
    public function intercept(
        ServerRequestInterface $request,
        ResponseInterface $response,
        object $intercepted,
    ): HttpInterceptableInterface|HttpResponderInterface|ResponseInterface;
}
