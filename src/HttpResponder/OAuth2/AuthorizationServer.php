<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\HttpResponder\OAuth2;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\HttpResponder\OAuth2;
use Distantmagic\Resonance\HttpResponder\PsrResponder;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\PsrServerRequestConverter;
use League\OAuth2\Server\AuthorizationServer as LeagueAuthorizationServer;
use Nyholm\Psr7\Factory\Psr17Factory;
use Swoole\Http\Request;
use Swoole\Http\Response;

#[Singleton]
readonly class AuthorizationServer extends OAuth2
{
    public function __construct(
        private LeagueAuthorizationServer $leagueAuthorizationServer,
        private PsrServerRequestConverter $psrServerRequestConverter,
        private Psr17Factory $psr17Factory,
    ) {}

    public function respond(Request $request, Response $response): HttpResponderInterface
    {
        $serverRequest = $this->psrServerRequestConverter->convertToServerRequest($request);
        $psrResponse = $this->leagueAuthorizationServer->respondToAccessTokenRequest(
            $serverRequest,
            $this->psr17Factory->createResponse(),
        );

        return new PsrResponder($psrResponse);
    }
}
