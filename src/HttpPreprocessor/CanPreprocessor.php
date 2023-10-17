<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpPreprocessor;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\Can;
use Distantmagic\Resonance\Attribute\PreprocessesHttpResponder;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Gatekeeper;
use Distantmagic\Resonance\HttpPreprocessor;
use Distantmagic\Resonance\HttpResponder\Error\Forbidden;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\SingletonCollection;
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * @template-extends HttpPreprocessor<Can>
 */
#[PreprocessesHttpResponder(
    attribute: Can::class,
    priority: 1000,
)]
#[Singleton(collection: SingletonCollection::HttpPreprocessor)]
readonly class CanPreprocessor extends HttpPreprocessor
{
    public function __construct(
        private Forbidden $forbidden,
        private Gatekeeper $gatekeeper,
    ) {}

    public function preprocess(
        Request $request,
        Response $response,
        Attribute $attribute,
        HttpResponderInterface $next,
    ): HttpResponderInterface {
        $gatekeeperUserContext = $this->gatekeeper->withRequest($request);

        if (!$gatekeeperUserContext->can($attribute->siteAction)) {
            return $this->forbidden;
        }

        return $next;
    }
}
