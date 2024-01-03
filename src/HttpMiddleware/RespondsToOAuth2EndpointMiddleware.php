<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpMiddleware;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\HandlesMiddlewareAttribute;
use Distantmagic\Resonance\Attribute\RespondsToOAuth2Endpoint;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpMiddleware;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\OAuth2AuthorizationCodeFlowControllerInterface;
use Distantmagic\Resonance\OAuth2Endpoint;
use Distantmagic\Resonance\SingletonCollection;
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * @template-extends HttpMiddleware<RespondsToOAuth2Endpoint>
 */
#[HandlesMiddlewareAttribute(
    attribute: RespondsToOAuth2Endpoint::class,
    priority: 900,
)]
#[Singleton(
    collection: SingletonCollection::HttpMiddleware,
    grantsFeature: Feature::OAuth2,
)]
readonly class RespondsToOAuth2EndpointMiddleware extends HttpMiddleware
{
    public function __construct(
        private OAuth2AuthorizationCodeFlowControllerInterface $authorizationCodeFlowController,
    ) {}

    public function preprocess(
        Request $request,
        Response $response,
        Attribute $attribute,
        HttpInterceptableInterface|HttpResponderInterface $next,
    ): HttpInterceptableInterface|HttpResponderInterface {
        switch ($attribute->endpoint) {
            case OAuth2Endpoint::ClientScopeConsentForm:
                $this
                    ->authorizationCodeFlowController
                    ->prepareConsentRequest($request, $response)
                ;

                break;
        }

        return $next;
    }
}
