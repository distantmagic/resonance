<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpPreprocessor;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\InterceptableInternalRedirect;
use Distantmagic\Resonance\Attribute\PreprocessesHttpResponder;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpPreprocessor;
use Distantmagic\Resonance\HttpResponder\Redirect;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\InternalLinkBuilder;
use Distantmagic\Resonance\InternalRedirect;
use Distantmagic\Resonance\SingletonCollection;
use LogicException;
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * @template-extends HttpPreprocessor<InterceptableInternalRedirect>
 */
#[PreprocessesHttpResponder(
    attribute: InterceptableInternalRedirect::class,
    priority: 100,
)]
#[Singleton(collection: SingletonCollection::HttpPreprocessor)]
readonly class InternalRedirectInterceptor extends HttpPreprocessor
{
    public function __construct(private InternalLinkBuilder $internalLinkBuilder) {}

    public function preprocess(
        Request $request,
        Response $response,
        Attribute $attribute,
        HttpInterceptableInterface|HttpResponderInterface $next,
    ): HttpResponderInterface {
        if (!($next instanceof InternalRedirect)) {
            throw new LogicException('Expected '.InternalRedirect::class);
        }

        return new Redirect($this->internalLinkBuilder->build(
            $next->routeSymbol,
            $next->params,
        ));
    }
}
