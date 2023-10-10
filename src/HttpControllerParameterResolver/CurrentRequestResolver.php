<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpControllerParameterResolver;

use Distantmagic\Resonance\Attribute\CurrentRequest;
use Distantmagic\Resonance\Attribute\ResolvesHttpControllerParameter;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpControllerParameter;
use Distantmagic\Resonance\HttpControllerParameterResolver;
use Distantmagic\Resonance\SingletonCollection;
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * @template-extends HttpControllerParameterResolver<CurrentRequest>
 */
#[ResolvesHttpControllerParameter(CurrentRequest::class)]
#[Singleton(collection: SingletonCollection::HttpControllerParameterResolver)]
readonly class CurrentRequestResolver extends HttpControllerParameterResolver
{
    public function resolve(
        Request $request,
        Response $response,
        HttpControllerParameter $parameter,
    ): mixed {
        return $request;
    }
}
