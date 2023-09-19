<?php

declare(strict_types=1);

namespace Resonance\WebSocketProtocolController;

use App\CSRFManager;
use App\InputValidator\RPCMessageValidator;
use App\SessionAuthentication;
use App\SingletonCollection;
use App\SiteAction;
use Ds\Map;
use JsonException;
use Nette\Schema\ValidationException;
use Psr\Log\LoggerInterface;
use Resonance\Attribute\ControlsWebSocketProtocol;
use Resonance\Attribute\Singleton;
use Resonance\Gatekeeper;
use Resonance\WebSocketAuthResolution;
use Resonance\WebSocketConnection;
use Resonance\WebSocketConnectionController\RPCConnectionController;
use Resonance\WebSocketProtocol;
use Resonance\WebSocketProtocolController;
use Resonance\WebSocketProtocolException;
use Resonance\WebSocketRPCResponderAggregate;
use RuntimeException;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use Throwable;

use function Sentry\captureException;

#[ControlsWebSocketProtocol(WebSocketProtocol::RPC)]
#[Singleton(collection: SingletonCollection::WebSocketProtocolController)]
final readonly class RPCProtocolController extends WebSocketProtocolController
{
    /**
     * @var Map<int, RPCConnectionController>
     */
    private Map $connectionControllers;

    private RPCMessageValidator $rpcMessageValidator;

    public function __construct(
        private CSRFManager $csrfManager,
        private Gatekeeper $gatekeeper,
        private LoggerInterface $logger,
        private SessionAuthentication $sessionAuthentication,
        private WebSocketRPCResponderAggregate $webSocketRPCResponderAggregate,
    ) {
        /**
         * @var Map<int, RPCConnectionController>
         */
        $this->connectionControllers = new Map();
        $this->rpcMessageValidator = new RPCMessageValidator();
    }

    public function isAuthorizedToConnect(Request $request): WebSocketAuthResolution
    {
        if (!is_array($request->get) || !$this->csrfManager->checkToken($request, $request->get)) {
            return new WebSocketAuthResolution(false);
        }

        $user = $this->sessionAuthentication->authenticatedUser($request);

        return new WebSocketAuthResolution(
            $this->gatekeeper->withUser($user)->can(SiteAction::StartWebSocketRPCConnection),
            $user,
        );
    }

    public function onClose(Server $server, int $fd): void
    {
        if (!$this->connectionControllers->hasKey($fd)) {
            throw new RuntimeException(sprintf(
                'RPC connection controller is not set and therefore it cannot be removed: %s',
                $fd,
            ));
        }

        $this->connectionControllers->remove($fd);
    }

    public function onMessage(Server $server, Frame $frame): void
    {
        try {
            /**
             * @var mixed $decodedRpcMessage explicitly mixed for typechecks
             */
            $decodedRpcMessage = json_decode(
                json: $frame->data,
                flags: JSON_THROW_ON_ERROR,
            );

            $this->onJsonMessage($server, $frame, $decodedRpcMessage);
        } catch (JsonException $exception) {
            $this->onProtocolError($server, $frame, 'Invalid JSON');
            $this->onException($server, $frame, $exception);
        } catch (WebSocketProtocolException $exception) {
            $this->onProtocolError($server, $frame, $exception->getMessage());
            $this->onException($server, $frame, $exception);
        }
    }

    public function onOpen(Server $server, int $fd, WebSocketAuthResolution $webSocketAuthResolution): void
    {
        $connectionController = new RPCConnectionController(
            $this->webSocketRPCResponderAggregate,
            $webSocketAuthResolution,
            new WebSocketConnection($server, $fd),
        );

        $this->connectionControllers->put($fd, $connectionController);
    }

    private function getFrameController(Frame $frame): RPCConnectionController
    {
        if (!$this->connectionControllers->hasKey($frame->fd)) {
            throw new RuntimeException(sprintf(
                'RPC connection controller is not set and therefore it cannot handle a message: %s',
                $frame->fd,
            ));
        }

        return $this->connectionControllers->get($frame->fd);
    }

    private function onException(Server $server, Frame $frame, Throwable $exception): void
    {
        captureException($exception);
        $this->logger->debug((string) $exception);
    }

    private function onJsonMessage(Server $server, Frame $frame, mixed $jsonMessage): void
    {
        try {
            $this
                ->getFrameController($frame)
                ->onRPCMessage($this->rpcMessageValidator->validateData($jsonMessage))
            ;
        } catch (ValidationException $exception) {
            $this->onProtocolError($server, $frame, $exception->getMessage());
        }
    }

    private function onProtocolError(Server $server, Frame $frame, string $reason): void
    {
        $server->disconnect($frame->fd, SWOOLE_WEBSOCKET_CLOSE_PROTOCOL_ERROR, $reason);
    }
}
