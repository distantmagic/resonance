<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpMiddleware;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\Can;
use Distantmagic\Resonance\Attribute\PreprocessesHttpResponder;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Gatekeeper;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpMiddleware;
use Distantmagic\Resonance\HttpResponder\Error\Forbidden;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\SingletonCollection;
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * @template-extends HttpMiddleware<Can>
 */
#[PreprocessesHttpResponder(
    attribute: Can::class,
    priority: 1000,
)]
#[Singleton(collection: SingletonCollection::HttpMiddleware)]
readonly class CanPreprocessor extends HttpMiddleware
{
    public function __construct(
        private Forbidden $forbidden,
        private Gatekeeper $gatekeeper,
    ) {}

    public function preprocess(
        Request $request,
        Response $response,
        Attribute $attribute,
        HttpInterceptableInterface|HttpResponderInterface $next,
    ): HttpInterceptableInterface|HttpResponderInterface {
        $gatekeeperUserContext = $this->gatekeeper->withRequest($request);

        if (!$gatekeeperUserContext->can($attribute->siteAction)) {
            return $this->forbidden;
        }

        return $next;
    }
}
