<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Response;

/**
 * @template TAttribute of Attribute
 */
interface HttpControllerParameterResolverInterface
{
    /**
     * @param HttpControllerParameter<TAttribute> $parameter
     * @param TAttribute                          $attribute
     */
    public function resolve(
        ServerRequestInterface $request,
        Response $response,
        HttpControllerParameter $parameter,
        Attribute $attribute,
    ): HttpControllerParameterResolution;
}
