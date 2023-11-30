<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\HttpResponder\PsrResponder;
use League\OAuth2\Server\AuthorizationServer as LeagueAuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use League\OAuth2\Server\ResponseTypes\RedirectResponse;
use Nyholm\Psr7\Factory\Psr17Factory;
use Swoole\Http\Request;
use Swoole\Http\Response;

abstract readonly class OAuth2AuthorizationCodeFlowController implements OAuth2AuthorizationCodeFlowControllerInterface
{
    public function __construct(
        private LeagueAuthorizationServer $leagueAuthorizationServer,
        private OAuth2AuthorizationRequestSessionStore $authorizationRequestSessionStore,
        private Psr17Factory $psr17Factory,
        private SessionAuthentication $sessionAuthentication,
    ) {}

    public function completeConsentRequest(
        Request $request,
        Response $response,
        bool $userConsented,
    ): HttpResponderInterface {
        $authorizationRequest = $this->authorizationRequestSessionStore->get($request, $response);
        $authorizationRequest->setAuthorizationApproved($userConsented);

        try {
            $psrResponse = $this
                ->leagueAuthorizationServer
                ->completeAuthorizationRequest(
                    $authorizationRequest,
                    $this->psr17Factory->createResponse(),
                )
            ;

            if ($psrResponse->hasHeader('Location')) {
                $locations = $psrResponse->getHeader('Location');
                $psrResponse = $psrResponse->withHeader('Access-Control-Allow-Origin', $locations);
            }

            return new PsrResponder($psrResponse);
        } catch (OAuthServerException $exception) {
            $psrResponse = $this->psr17Factory->createResponse();

            return new PsrResponder($exception->generateHttpResponse($psrResponse));
        }
    }

    public function obtainAuthenticatedUser(
        Request $request,
        Response $response,
        AuthorizationRequest $authorizationRequest,
    ): null|HttpInterceptableInterface|HttpResponderInterface {
        $authenticatedUser = $this->sessionAuthentication->getAuthenticatedUser($request);

        if ($authenticatedUser) {
            $authorizationRequest->setUser(new OAuth2AuthorizedUser($authenticatedUser->getIdentifier()));

            return null;
        }

        return $this->redirectToLoginPage($request, $response, $authorizationRequest);
    }
}
