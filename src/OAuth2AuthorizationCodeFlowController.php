<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpResponder\PsrResponder;
use League\OAuth2\Server\AuthorizationServer as LeagueAuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Swoole\Http\Response;

#[GrantsFeature(Feature::OAuth2)]
#[Singleton(provides: OAuth2AuthorizationCodeFlowControllerInterface::class)]
readonly class OAuth2AuthorizationCodeFlowController implements OAuth2AuthorizationCodeFlowControllerInterface
{
    public function __construct(
        private ContentSecurityPolicyRulesRepository $contentSecurityPolicyRulesRepository,
        private LeagueAuthorizationServer $leagueAuthorizationServer,
        private OAuth2AuthorizationRequestSessionStore $authorizationRequestSessionStore,
        private OAuth2EndpointResponderAggregate $oAuth2EndpointResponderAggregate,
        private Psr17Factory $psr17Factory,
        private SessionAuthentication $sessionAuthentication,
    ) {}

    public function completeConsentRequest(
        ServerRequestInterface $request,
        Response $response,
        bool $userConsented,
    ): HttpResponderInterface {
        $authorizationRequest = $this->authorizationRequestSessionStore->get($request, $response);
        $authorizationRequest->setAuthorizationApproved($userConsented);

        $this->authorizationRequestSessionStore->clear($request, $response);

        try {
            $psrResponse = $this
                ->leagueAuthorizationServer
                ->completeAuthorizationRequest(
                    $authorizationRequest,
                    $this->psr17Factory->createResponse(),
                )
            ;

            return new PsrResponder($psrResponse);
        } catch (OAuthServerException $exception) {
            $psrResponse = $this->psr17Factory->createResponse();

            return new PsrResponder($exception->generateHttpResponse($psrResponse));
        }
    }

    public function obtainSessionAuthenticatedUser(
        ServerRequestInterface $request,
        Response $response,
        AuthorizationRequest $authorizationRequest,
    ): null|HttpInterceptableInterface|HttpResponderInterface {
        $authenticatedUser = $this->sessionAuthentication->getAuthenticatedUser($request);

        if ($authenticatedUser) {
            $authorizationRequest->setUser(new OAuth2AuthorizedUser($authenticatedUser->user->getIdentifier()));

            return null;
        }

        return $this->redirectToLoginPage($request, $response, $authorizationRequest);
    }

    public function prepareConsentRequest(
        ServerRequestInterface $request,
        Response $response,
    ): void {
        if (!$this->authorizationRequestSessionStore->has($request, $response)) {
            throw new RuntimeException('Authorization request is not in progress');
        }

        $authorizationRequest = $this->authorizationRequestSessionStore->get($request, $response);

        $formAction = $this
            ->contentSecurityPolicyRulesRepository
            ->from($request)
            ->formAction
        ;

        $redirectUris = $authorizationRequest->getClient()->getRedirectUri();

        if (is_array($redirectUris)) {
            foreach ($redirectUris as $redirectUri) {
                $formAction->add($redirectUri);
            }
        } else {
            $formAction->add($redirectUris);
        }
    }

    public function redirectToAuthenticatedPage(
        ServerRequestInterface $request,
        Response $response,
    ): HttpInterceptableInterface {
        $routeSymbol = $this
            ->oAuth2EndpointResponderAggregate
            ->getHttpRouteSymbolForEndpoint(OAuth2Endpoint::AuthenticatedPage)
        ;

        return new InternalRedirect($routeSymbol);
    }

    public function redirectToClientScopeConsentPage(
        ServerRequestInterface $request,
        Response $response,
        AuthorizationRequest $authorizationRequest,
    ): HttpInterceptableInterface {
        $routeSymbol = $this
            ->oAuth2EndpointResponderAggregate
            ->getHttpRouteSymbolForEndpoint(OAuth2Endpoint::ClientScopeConsentForm)
        ;

        return new InternalRedirect($routeSymbol);
    }

    public function redirectToLoginPage(
        ServerRequestInterface $request,
        Response $response,
        AuthorizationRequest $authorizationRequest,
    ): HttpInterceptableInterface {
        $routeSymbol = $this
            ->oAuth2EndpointResponderAggregate
            ->getHttpRouteSymbolForEndpoint(OAuth2Endpoint::LoginForm)
        ;

        return new InternalRedirect($routeSymbol);
    }
}
