<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Swoole\Http\Request;
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
        Request $request,
        Response $response,
        object $intercepted,
    ): null|HttpInterceptableInterface|HttpResponderInterface;
}
