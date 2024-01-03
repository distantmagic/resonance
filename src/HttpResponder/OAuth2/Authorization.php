<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder\OAuth2;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpResponder\PsrResponder;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\OAuth2AuthorizationCodeFlowControllerInterface;
use Distantmagic\Resonance\OAuth2AuthorizationRequestSessionStore;
use Distantmagic\Resonance\PsrServerRequestConverter;
use League\OAuth2\Server\AuthorizationServer as LeagueAuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Swoole\Http\Request;
use Swoole\Http\Response;

#[Singleton(grantsFeature: Feature::OAuth2)]
final readonly class Authorization extends HttpResponder
{
    public function __construct(
        private LeagueAuthorizationServer $leagueAuthorizationServer,
        private OAuth2AuthorizationCodeFlowControllerInterface $authorizationCodeFlowController,
        private OAuth2AuthorizationRequestSessionStore $authorizationRequestSessionStore,
        private PsrServerRequestConverter $psrServerRequestConverter,
        private Psr17Factory $psr17Factory,
    ) {}

    public function respond(Request $request, Response $response): HttpInterceptableInterface|HttpResponderInterface
    {
        $serverRequest = $this
            ->psrServerRequestConverter
            ->convertToServerRequest($request)
        ;

        try {
            $authRequest = $this
                ->leagueAuthorizationServer
                ->validateAuthorizationRequest($serverRequest)
            ;

        } catch (OAuthServerException $exception) {
            $psrResponse = $this->psr17Factory->createResponse();

            return new PsrResponder($exception->generateHttpResponse($psrResponse));
        }

        $this
            ->authorizationRequestSessionStore
            ->store($request, $response, $authRequest)
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
            ->redirectToClientScopeConsentPage($request, $response, $authRequest)
        ;
    }
}
