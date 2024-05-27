<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Amp\Http\Server\DefaultErrorHandler;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\SocketHttpServer;
use Distantmagic\Resonance\Attribute\RequiresBackendDriver;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PsrMessage\AmpServerRequest;
use Distantmagic\Resonance\PsrMessage\ServerResponse;
use Psr\Log\LoggerInterface;

use function Amp\trapSignal;

#[RequiresBackendDriver(BackendDriver::Amp)]
#[Singleton]
readonly class AmpServer implements RequestHandler
{
    private SocketHttpServer $server;

    public function __construct(
        private HttpResponderAggregate $httpResponderAggregate,
        LoggerInterface $logger,
        private PsrAmpResponder $psrAmpResponder,
        SwooleConfiguration $swooleConfiguration,
    ) {
        $this->server = SocketHttpServer::createForDirectAccess($logger);
        $this->server->expose(sprintf(
            '%s:%d',
            $swooleConfiguration->host,
            $swooleConfiguration->port,
        ));
    }

    public function __destruct() {}

    public function handleRequest(Request $request): Response
    {
        $request = new AmpServerRequest($request);

        return $this->psrAmpResponder->respondWithPsrResponse(
            request: $request,
            response: $this->httpResponderAggregate->respondToPsrRequest(
                request: $request,
                response: new ServerResponse(),
            ),
        );
    }

    public function start(): bool
    {
        $this->server->start($this, new DefaultErrorHandler());

        trapSignal([SIGINT, SIGTERM]);

        $this->server->stop();

        return true;
    }
}
