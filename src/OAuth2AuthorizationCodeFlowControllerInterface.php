<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Swoole\Http\Request;
use Swoole\Http\Response;

interface OAuth2AuthorizationCodeFlowControllerInterface
{
    public function obtainAuthenticatedUser(
        Request $request,
        Response $response,
        AuthorizationRequest $authorizationRequest,
    ): null|HttpInterceptableInterface|HttpResponderInterface;

    public function showClientScopeConsentPage(
        Request $request,
        Response $response,
        AuthorizationRequest $authorizationRequest,
    ): HttpInterceptableInterface|HttpResponderInterface;

    public function showLoginPage(
        Request $request,
        Response $response,
        AuthorizationRequest $authorizationRequest,
    ): HttpInterceptableInterface|HttpResponderInterface;
}
