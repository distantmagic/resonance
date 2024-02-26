<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Response;

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
        Response $response,
        object $intercepted,
    ): null|HttpInterceptableInterface|HttpResponderInterface;
}
