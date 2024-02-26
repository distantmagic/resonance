<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Response;

interface OAuth2AuthorizationCodeFlowControllerInterface
{
    public function completeConsentRequest(
        ServerRequestInterface $request,
        Response $response,
        bool $userConsented,
    ): HttpInterceptableInterface|HttpResponderInterface;

    public function obtainSessionAuthenticatedUser(
        ServerRequestInterface $request,
        Response $response,
        AuthorizationRequest $authorizationRequest,
    ): null|HttpInterceptableInterface|HttpResponderInterface;

    public function prepareConsentRequest(
        ServerRequestInterface $request,
        Response $response,
    ): void;

    public function redirectToAuthenticatedPage(
        ServerRequestInterface $request,
        Response $response,
    ): HttpInterceptableInterface|HttpResponderInterface;

    public function redirectToClientScopeConsentPage(
        ServerRequestInterface $request,
        Response $response,
        AuthorizationRequest $authorizationRequest,
    ): HttpInterceptableInterface|HttpResponderInterface;

    public function redirectToLoginPage(
        ServerRequestInterface $request,
        Response $response,
        AuthorizationRequest $authorizationRequest,
    ): HttpInterceptableInterface|HttpResponderInterface;
}
