<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @template TAttribute of Attribute
 */
interface HttpMiddlewareInterface
{
    /**
     * @param TAttribute $attribute
     */
    public function preprocess(
        ServerRequestInterface $request,
        ResponseInterface $response,
        Attribute $attribute,
        HttpInterceptableInterface|HttpResponderInterface $next,
    ): HttpInterceptableInterface|HttpResponderInterface|ResponseInterface;
}
