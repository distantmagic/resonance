<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder\OAuth2;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\HttpResponder\OAuth2;
use Distantmagic\Resonance\HttpResponder\PsrResponder;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\OAuth2AuthorizationRequestSessionStore;
use League\OAuth2\Server\AuthorizationServer as LeagueAuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Response;

#[GrantsFeature(Feature::OAuth2)]
#[Singleton]
readonly class AccessToken extends OAuth2
{
    public function __construct(
        private LeagueAuthorizationServer $leagueAuthorizationServer,
        private OAuth2AuthorizationRequestSessionStore $authorizationRequestSessionStore,
        private Psr17Factory $psr17Factory,
    ) {}

    public function respond(ServerRequestInterface $request, Response $response): HttpResponderInterface
    {
        try {
            $psrResponse = $this->leagueAuthorizationServer->respondToAccessTokenRequest(
                $request,
                $this->psr17Factory->createResponse(),
            );

            return new PsrResponder($psrResponse);
        } catch (OAuthServerException $exception) {
            $psrResponse = $this->psr17Factory->createResponse();

            return new PsrResponder($exception->generateHttpResponse($psrResponse));
        }
    }
}
