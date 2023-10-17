<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * @template TAttribute of Attribute
 */
interface HttpMiddlewareInterface
{
    /**
     * @param TAttribute $attribute
     */
    public function preprocess(
        Request $request,
        Response $response,
        Attribute $attribute,
        HttpInterceptableInterface|HttpResponderInterface $next,
    ): null|HttpInterceptableInterface|HttpResponderInterface;
}
