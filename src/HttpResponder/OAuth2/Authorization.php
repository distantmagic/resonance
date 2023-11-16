<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder\OAuth2;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpInterceptableInterface;
use Distantmagic\Resonance\HttpResponder;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\OAuth2AuthorizationCodeFlowControllerInterface;
use Distantmagic\Resonance\OAuth2AuthorizationRequestSessionStore;
use Distantmagic\Resonance\PsrServerRequestConverter;
use Distantmagic\Resonance\SingletonCollection;
use League\OAuth2\Server\AuthorizationServer as LeagueAuthorizationServer;
use Nyholm\Psr7\Factory\Psr17Factory;
use Swoole\Http\Request;
use Swoole\Http\Response;

#[Singleton(collection: SingletonCollection::HttpResponder)]
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

        $authRequest = $this
            ->leagueAuthorizationServer
            ->validateAuthorizationRequest($serverRequest)
        ;

        $this
            ->authorizationRequestSessionStore
            ->store($request, $response, $authRequest)
        ;

        $authUserResponse = $this
            ->authorizationCodeFlowController
            ->obtainAuthenticatedUser($request, $response, $authRequest)
        ;

        if ($authUserResponse) {
            return $authUserResponse;
        }

        return $this
            ->authorizationCodeFlowController
            ->showClientScopeConsentPage($request, $response, $authRequest)
        ;
    }
}
