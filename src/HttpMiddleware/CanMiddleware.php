<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpMiddleware;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\Can;
use Distantmagic\Resonance\Attribute\HandlesMiddlewareAttribute;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Gatekeeper;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpMiddleware;
use Distantmagic\Resonance\HttpResponder\Error\Forbidden;
use Distantmagic\Resonance\HttpResponderCollection;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\SingletonCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @template-extends HttpMiddleware<Can>
 */
#[HandlesMiddlewareAttribute(
    attribute: Can::class,
    priority: 1000,
)]
#[Singleton(collection: SingletonCollection::HttpMiddleware)]
readonly class CanMiddleware extends HttpMiddleware
{
    public function __construct(
        private Forbidden $forbidden,
        private Gatekeeper $gatekeeper,
        private HttpResponderCollection $httpResponderCollection,
    ) {}

    public function preprocess(
        ServerRequestInterface $request,
        ResponseInterface $response,
        Attribute $attribute,
        HttpInterceptableInterface|HttpResponderInterface $next,
    ): HttpInterceptableInterface|HttpResponderInterface {
        $gatekeeperUserContext = $this->gatekeeper->withRequest($request);

        if ($gatekeeperUserContext->can($attribute->siteAction)) {
            return $next;
        }

        if ($attribute->onForbiddenRespondWith) {
            return $this
                ->httpResponderCollection
                ->httpResponders->get($attribute->onForbiddenRespondWith)
            ;
        }

        return $this->forbidden;
    }
}
