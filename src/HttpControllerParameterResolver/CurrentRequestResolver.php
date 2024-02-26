<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpControllerParameterResolver;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\CurrentRequest;
use Distantmagic\Resonance\Attribute\ResolvesHttpControllerParameter;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpControllerParameter;
use Distantmagic\Resonance\HttpControllerParameterResolution;
use Distantmagic\Resonance\HttpControllerParameterResolutionStatus;
use Distantmagic\Resonance\HttpControllerParameterResolver;
use Distantmagic\Resonance\SingletonCollection;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Response;

/**
 * @template-extends HttpControllerParameterResolver<CurrentRequest>
 */
#[ResolvesHttpControllerParameter(CurrentRequest::class)]
#[Singleton(collection: SingletonCollection::HttpControllerParameterResolver)]
readonly class CurrentRequestResolver extends HttpControllerParameterResolver
{
    public function resolve(
        ServerRequestInterface $request,
        Response $response,
        HttpControllerParameter $parameter,
        Attribute $attribute,
    ): HttpControllerParameterResolution {
        return new HttpControllerParameterResolution(
            HttpControllerParameterResolutionStatus::Success,
            $request
        );
    }
}
