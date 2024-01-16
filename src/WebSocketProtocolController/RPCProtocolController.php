<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\WebSocketProtocolController;

use Distantmagic\Resonance\Attribute\ControlsWebSocketProtocol;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\AuthenticatedUserProvider;
use Distantmagic\Resonance\CSRFManager;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\Gatekeeper;
use Distantmagic\Resonance\InputValidator\RPCMessageValidator;
use Distantmagic\Resonance\JsonSerializer;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SiteAction;
use Distantmagic\Resonance\WebSocketAuthResolution;
use Distantmagic\Resonance\WebSocketConnection;
use Distantmagic\Resonance\WebSocketConnectionController\RPCConnectionController;
use Distantmagic\Resonance\WebSocketProtocol;
use Distantmagic\Resonance\WebSocketProtocolController;
use Distantmagic\Resonance\WebSocketProtocolException;
use Distantmagic\Resonance\WebSocketRPCResponderAggregate;
use Ds\Map;
use JsonException;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use Throwable;

#[ControlsWebSocketProtocol(WebSocketProtocol::RPC)]
#[Singleton(
    collection: SingletonCollection::WebSocketProtocolController,
    grantsFeature: Feature::WebSocket,
)]
final readonly class RPCProtocolController extends WebSocketProtocolController
{
    /**
     * @var Map<int, RPCConnectionController>
     */
    private Map $connectionControllers;

    public function __construct(
        private CSRFManager $csrfManager,
        private AuthenticatedUserProvider $authenticatedUserProvider,
        private Gatekeeper $gatekeeper,
        private JsonSerializer $jsonSerializer,
        private LoggerInterface $logger,
        private RPCMessageValidator $rpcMessageValidator,
        private WebSocketRPCResponderAggregate $webSocketRPCResponderAggregate,
    ) {
        /**
         * @var Map<int, RPCConnectionController>
         */
        $this->connectionControllers = new Map();
    }

    public function isAuthorizedToConnect(Request $request): WebSocketAuthResolution
    {
        if (!is_array($request->get) || !$this->csrfManager->checkToken($request, $request->get)) {
            return new WebSocketAuthResolution(false);
        }

        $user = $this->authenticatedUserProvider->getAuthenticatedUser($request);

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
            $decodedRpcMessage = $this->jsonSerializer->unserialize($frame->data);

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
        $this->logger->debug((string) $exception);
    }

    private function onJsonMessage(Server $server, Frame $frame, mixed $jsonMessage): void
    {
        $inputValidationResult = $this->rpcMessageValidator->validateData($jsonMessage);

        if ($inputValidationResult->inputValidatedData) {
            $this
                ->getFrameController($frame)
                ->onRPCMessage($inputValidationResult->inputValidatedData)
            ;
        } else {
            $this->onProtocolError($server, $frame, $inputValidationResult->getErrorMessage());
        }
    }

    private function onProtocolError(Server $server, Frame $frame, string $reason): void
    {
        $server->disconnect($frame->fd, SWOOLE_WEBSOCKET_CLOSE_PROTOCOL_ERROR, $reason);
    }
}
