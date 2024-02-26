<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface OAuth2AuthorizationCodeFlowControllerInterface
{
    public function completeConsentRequest(
        ServerRequestInterface $request,
        ResponseInterface $response,
        bool $userConsented,
    ): HttpInterceptableInterface|HttpResponderInterface|ResponseInterface;

    public function obtainSessionAuthenticatedUser(
        ServerRequestInterface $request,
        ResponseInterface $response,
        AuthorizationRequest $authorizationRequest,
    ): null|HttpInterceptableInterface|HttpResponderInterface|ResponseInterface;

    public function prepareConsentRequest(ServerRequestInterface $request): void;

    public function redirectToAuthenticatedPage(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): HttpInterceptableInterface|HttpResponderInterface|ResponseInterface;

    public function redirectToClientScopeConsentPage(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): HttpInterceptableInterface|HttpResponderInterface|ResponseInterface;

    public function redirectToLoginPage(
        ServerRequestInterface $request,
        ResponseInterface $response,
    ): HttpInterceptableInterface|HttpResponderInterface|ResponseInterface;
}
