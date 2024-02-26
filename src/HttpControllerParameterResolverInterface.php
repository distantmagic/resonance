<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
        ResponseInterface $response,
        HttpControllerParameter $parameter,
        Attribute $attribute,
    ): HttpControllerParameterResolution;
}
