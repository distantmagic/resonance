<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Swoole\Http\Request;
use Swoole\Http\Response;

interface OAuth2AuthorizationCodeFlowControllerInterface
{
    public function completeConsentRequest(
        Request $request,
        Response $response,
        bool $userConsented,
    ): HttpInterceptableInterface|HttpResponderInterface;

    public function obtainAuthenticatedUser(
        Request $request,
        Response $response,
        AuthorizationRequest $authorizationRequest,
    ): null|HttpInterceptableInterface|HttpResponderInterface;

    public function prepareConsentRequest(
        Request $request,
        Response $response,
    ): void;

    public function redirectToClientScopeConsentPage(
        Request $request,
        Response $response,
        AuthorizationRequest $authorizationRequest,
    ): HttpInterceptableInterface|HttpResponderInterface;

    public function redirectToLoginPage(
        Request $request,
        Response $response,
        AuthorizationRequest $authorizationRequest,
    ): HttpInterceptableInterface|HttpResponderInterface;
}
