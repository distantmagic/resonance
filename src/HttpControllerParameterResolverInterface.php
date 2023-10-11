<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Swoole\Http\Request;
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
        Request $request,
        Response $response,
        HttpControllerParameter $parameter,
        Attribute $attribute,
    ): HttpControllerParameterResolution;
}
