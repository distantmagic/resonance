<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder\OAuth2;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\OAuth2AuthorizationCodeFlowControllerInterface;
use Distantmagic\Resonance\OAuth2AuthorizationRequestSessionStore;
use League\OAuth2\Server\AuthorizationServer as LeagueAuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[GrantsFeature(Feature::OAuth2)]
#[Singleton]
final readonly class Authorization extends HttpResponder
{
    public function __construct(
        private LeagueAuthorizationServer $leagueAuthorizationServer,
        private OAuth2AuthorizationCodeFlowControllerInterface $authorizationCodeFlowController,
        private OAuth2AuthorizationRequestSessionStore $authorizationRequestSessionStore,
    ) {}

    public function respond(ServerRequestInterface $request, ResponseInterface $response): HttpInterceptableInterface|HttpResponderInterface|ResponseInterface
    {
        try {
            $authRequest = $this
                ->leagueAuthorizationServer
                ->validateAuthorizationRequest($request)
            ;
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
        }

        $this
            ->authorizationRequestSessionStore
            ->store($request, $authRequest)
        ;

        $authUserResponse = $this
            ->authorizationCodeFlowController
            ->obtainSessionAuthenticatedUser($request, $response, $authRequest)
        ;

        if ($authUserResponse) {
            return $authUserResponse;
        }

        return $this
            ->authorizationCodeFlowController
            ->redirectToClientScopeConsentPage($request, $response)
        ;
    }
}
