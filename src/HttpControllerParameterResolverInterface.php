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
     * @param TAttribute   $responderAttribute
     * @param class-string $parameterClass
     */
    public function resolve(
        Request $request,
        Response $response,
        Attribute $responderAttribute,
        string $parameterClass,
    ): mixed;
}
