<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\Singleton;
use League\OAuth2\Server\AuthorizationServer as LeagueAuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

#[GrantsFeature(Feature::OAuth2)]
#[Singleton(provides: OAuth2AuthorizationCodeFlowControllerInterface::class)]
readonly class OAuth2AuthorizationCodeFlowController implements OAuth2AuthorizationCodeFlowControllerInterface
{
    public function __construct(
        private ContentSecurityPolicyRulesRepository $contentSecurityPolicyRulesRepository,
        private LeagueAuthorizationServer $leagueAuthorizationServer,
        private OAuth2AuthorizationRequestSessionStore $authorizationRequestSessionStore,
        private OAuth2EndpointResponderAggregate $oAuth2EndpointResponderAggregate,
        private SessionAuthentication $sessionAuthentication,
    ) {}

    public function completeConsentRequest(
        ServerRequestInterface $request,
        ResponseInterface $response,
        bool $userConsented,
    ): ResponseInterface {
        $authorizationRequest = $this->authorizationRequestSessionStore->get($request);
        $authorizationRequest->setAuthorizationApproved($userConsented);

        $this->authorizationRequestSessionStore->clear($request);

        try {
            return $this->leagueAuthorizationServer->completeAuthorizationRequest(
                $authorizationRequest,
                $response,
            );
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
        }
    }

    public function obtainSessionAuthenticatedUser(
        ServerRequestInterface $request,
        ResponseInterface $response,
        AuthorizationRequest $authorizationRequest,
    ): null|HttpInterceptableInterface|HttpResponderInterface|ResponseInterface {
        $authenticatedUser = $this->sessionAuthentication->getAuthenticatedUser($request);

        if ($authenticatedUser) {
            $authorizationRequest->setUser(new OAuth2AuthorizedUser($authenticatedUser->user->getIdentifier()));

            return null;
        }

        return $this->redirectToLoginPage($request, $response);
    }

    public function prepareConsentRequest(ServerRequestInterface $request): void
    {
        if (!$this->authorizationRequestSessionStore->has($request)) {
            throw new RuntimeException('Authorization request is not in progress');
        }

        $authorizationRequest = $this->authorizationRequestSessionStore->get($request);

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
        ResponseInterface $response,
    ): HttpInterceptableInterface {
        $routeSymbol = $this
            ->oAuth2EndpointResponderAggregate
            ->getHttpRouteSymbolForEndpoint(OAuth2Endpoint::AuthenticatedPage)
        ;

        return new InternalRedirect($routeSymbol);
    }

    public function redirectToClientScopeConsentPage(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): HttpInterceptableInterface {
        $routeSymbol = $this
            ->oAuth2EndpointResponderAggregate
            ->getHttpRouteSymbolForEndpoint(OAuth2Endpoint::ClientScopeConsentForm)
        ;

        return new InternalRedirect($routeSymbol);
    }

    public function redirectToLoginPage(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): HttpInterceptableInterface {
        $routeSymbol = $this
            ->oAuth2EndpointResponderAggregate
            ->getHttpRouteSymbolForEndpoint(OAuth2Endpoint::LoginForm)
        ;

        return new InternalRedirect($routeSymbol);
    }
}
