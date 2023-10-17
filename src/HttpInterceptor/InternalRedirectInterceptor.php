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
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * @template-extends HttpInterceptor<InternalRedirect>
 */
#[Intercepts(InternalRedirect::class)]
#[Singleton(collection: SingletonCollection::HttpInterceptor)]
readonly class InternalRedirectInterceptor extends HttpInterceptor
{
    public function __construct(private InternalLinkBuilder $internalLinkBuilder) {}

    public function intercept(
        Request $request,
        Response $response,
        object $intercepted,
    ): HttpResponderInterface {
        return new Redirect($this->internalLinkBuilder->build(
            $intercepted->routeSymbol,
            $intercepted->params,
        ));
    }
}
