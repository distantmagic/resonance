<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpMiddleware;

use Distantmagic\Resonance\Attribute;
use Distantmagic\Resonance\Attribute\HandlesMiddlewareAttribute;
use Distantmagic\Resonance\Attribute\RespondsWithOAuth2ScopeConsentForm;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpMiddleware;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\OAuth2AuthorizationCodeFlowControllerInterface;
use Distantmagic\Resonance\SingletonCollection;
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * @template-extends HttpMiddleware<RespondsWithOAuth2ScopeConsentForm>
 */
#[HandlesMiddlewareAttribute(
    attribute: RespondsWithOAuth2ScopeConsentForm::class,
    priority: 900,
)]
#[Singleton(collection: SingletonCollection::HttpMiddleware)]
readonly class RespondsWithOAuth2ScopeConsentFormMiddleware extends HttpMiddleware
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
        $this
            ->authorizationCodeFlowController
            ->prepareConsentRequest($request, $response)
        ;

        return $next;
    }
}
