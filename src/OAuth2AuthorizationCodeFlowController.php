<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Swoole\Http\Request;
use Swoole\Http\Response;

abstract readonly class OAuth2AuthorizationCodeFlowController implements OAuth2AuthorizationCodeFlowControllerInterface
{
    public function __construct(
        private SessionAuthentication $sessionAuthentication,
    ) {}

    public function obtainAuthenticatedUser(
        Request $request,
        Response $response,
        AuthorizationRequest $authorizationRequest,
    ): null|HttpInterceptableInterface|HttpResponderInterface {
        $authenticatedUser = $this->sessionAuthentication->getAuthenticatedUser($request);

        if ($authenticatedUser) {
            $authorizationRequest->setUser(new OAuth2AuthorizedUser($authenticatedUser->getIdentifier()));
        }

        return $this->redirectToLoginPage($request, $response, $authorizationRequest);
    }
}
