<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpMiddleware;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\GrantsFeature;
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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @template-extends HttpMiddleware<RespondsToOAuth2Endpoint>
 */
#[GrantsFeature(Feature::OAuth2)]
#[HandlesMiddlewareAttribute(
    attribute: RespondsToOAuth2Endpoint::class,
    priority: 900,
)]
#[Singleton(collection: SingletonCollection::HttpMiddleware)]
readonly class RespondsToOAuth2EndpointMiddleware extends HttpMiddleware
{
    public function __construct(
        private OAuth2AuthorizationCodeFlowControllerInterface $authorizationCodeFlowController,
    ) {}

    public function preprocess(
        ServerRequestInterface $request,
        ResponseInterface $response,
        Attribute $attribute,
        HttpInterceptableInterface|HttpResponderInterface $next,
    ): HttpInterceptableInterface|HttpResponderInterface {
        if (OAuth2Endpoint::ClientScopeConsentForm === $attribute->endpoint) {
            $this->authorizationCodeFlowController->prepareConsentRequest($request);
        }

        return $next;
    }
}
