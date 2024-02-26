<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpInterceptor;

use Distantmagic\Resonance\Attribute\Intercepts;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpInterceptor;
use Distantmagic\Resonance\HttpResponder\Redirect;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\InternalLinkBuilder;
use Distantmagic\Resonance\InternalRedirect;
use Distantmagic\Resonance\SingletonCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @template-extends HttpInterceptor<InternalRedirect>
 */
#[Intercepts(InternalRedirect::class)]
#[Singleton(collection: SingletonCollection::HttpInterceptor)]
readonly class InternalRedirectInterceptor extends HttpInterceptor
{
    public function __construct(private InternalLinkBuilder $internalLinkBuilder) {}

    public function intercept(
        ServerRequestInterface $request,
        ResponseInterface $response,
        object $intercepted,
    ): HttpResponderInterface {
        return new Redirect($this->internalLinkBuilder->build(
            $intercepted->routeSymbol,
            $intercepted->params,
        ));
    }
}
