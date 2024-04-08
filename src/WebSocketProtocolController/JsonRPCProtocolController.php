<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\WebSocketProtocolController;

use Distantmagic\Resonance\Attribute\ControlsWebSocketProtocol;
use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\AuthenticatedUserStoreAggregate;
use Distantmagic\Resonance\ConstraintResultErrorMessage;
use Distantmagic\Resonance\CSRFManager;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\Gatekeeper;
use Distantmagic\Resonance\InputValidator\JsonRPCMessageValidator;
use Distantmagic\Resonance\InputValidatorController;
use Distantmagic\Resonance\JsonSerializer;
use Distantmagic\Resonance\SingletonCollection;
use Distantmagic\Resonance\SiteAction;
use Distantmagic\Resonance\WebSocketAuthResolution;
use Distantmagic\Resonance\WebSocketConnection;
use Distantmagic\Resonance\WebSocketConnectionStatus;
use Distantmagic\Resonance\WebSocketJsonRPCConnectionControllerInterface;
use Distantmagic\Resonance\WebSocketJsonRPCConnectionHandle;
use Distantmagic\Resonance\WebSocketJsonRPCResponderAggregate;
use Distantmagic\Resonance\WebSocketProtocol;
use Distantmagic\Resonance\WebSocketProtocolController;
use Distantmagic\Resonance\WebSocketProtocolException;
use Ds\Map;
use JsonException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use Throwable;

#[ControlsWebSocketProtocol(WebSocketProtocol::JsonRPC)]
#[GrantsFeature(Feature::WebSocket)]
#[Singleton(collection: SingletonCollection::WebSocketProtocolController)]
final readonly class JsonRPCProtocolController extends WebSocketProtocolController
{
    /**
     * @var Map<int,WebSocketJsonRPCConnectionHandle>
     */
    private Map $connectionHandles;

    public function __construct(
        private CSRFManager $csrfManager,
        private AuthenticatedUserStoreAggregate $authenticatedUserStoreAggregate,
        private Gatekeeper $gatekeeper,
        private InputValidatorController $inputValidatorController,
        private JsonRPCMessageValidator $rpcMessageValidator,
        private JsonSerializer $jsonSerializer,
        private LoggerInterface $logger,
        private WebSocketJsonRPCResponderAggregate $webSocketJsonRPCResponderAggregate,
        private ?WebSocketJsonRPCConnectionControllerInterface $webSocketJsonRPCConnectionController = null,
    ) {
        /**
         * @var Map<int,WebSocketJsonRPCConnectionHandle>
         */
        $this->connectionHandles = new Map();
    }

    public function isAuthorizedToConnect(ServerRequestInterface $request): WebSocketAuthResolution
    {
        $requestQueryParams = $request->getQueryParams();

        if (!isset($requestQueryParams['csrf_name']) || !is_string($requestQueryParams['csrf_name'])) {
            $this->logger->debug('WebSocket: CSRF name not found in query params');

            return new WebSocketAuthResolution(false);
        }

        if (!$this->csrfManager->checkToken(
            $request,
            $requestQueryParams['csrf_name'],
            $requestQueryParams
        )) {
            $this->logger->debug('WebSocket: Invalid CSRF token');

            return new WebSocketAuthResolution(false);
        }

        $user = $this->authenticatedUserStoreAggregate->getAuthenticatedUser($request);

        return new WebSocketAuthResolution(
            $this->gatekeeper->withUser($user)->can(SiteAction::StartWebSocketJsonRPCConnection),
            $user,
        );
    }

    public function onClose(int $fd): void
    {
        $connectionHandle = $this->connectionHandles->get($fd, null);

        if (!$connectionHandle) {
            throw new RuntimeException(sprintf(
                'RPC connection controller is not set and therefore it cannot be removed: %s',
                $fd,
            ));
        }

        $connectionHandle->webSocketConnection->status = WebSocketConnectionStatus::Closed;
        $connectionHandle->onClose();

        $this->webSocketJsonRPCConnectionController?->onClose(
            $connectionHandle->webSocketAuthResolution,
            $connectionHandle->webSocketConnection,
        );

        $this->connectionHandles->remove($fd);
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
            $this->onException($exception);
        } catch (WebSocketProtocolException $exception) {
            $this->onProtocolError($server, $frame, $exception->getMessage());
            $this->onException($exception);
        } catch (Throwable $throwable) {
            $this->onException($throwable);
        }
    }

    public function onOpen(WebSocketConnection $webSocketConnection, WebSocketAuthResolution $webSocketAuthResolution): void
    {
        $connectionHandle = new WebSocketJsonRPCConnectionHandle(
            $this->webSocketJsonRPCResponderAggregate,
            $webSocketAuthResolution,
            $webSocketConnection,
        );

        $this->webSocketJsonRPCConnectionController?->onOpen(
            $webSocketAuthResolution,
            $webSocketConnection,
        );

        $this->connectionHandles->put($webSocketConnection->fd, $connectionHandle);
    }

    private function getFrameController(Frame $frame): WebSocketJsonRPCConnectionHandle
    {
        if (!$this->connectionHandles->hasKey($frame->fd)) {
            throw new RuntimeException(sprintf(
                'RPC connection controller is not set and therefore it cannot handle a message: %s',
                $frame->fd,
            ));
        }

        return $this->connectionHandles->get($frame->fd);
    }

    private function onException(Throwable $exception): void
    {
        $this->logger->error((string) $exception);
    }

    private function onJsonMessage(Server $server, Frame $frame, mixed $jsonMessage): void
    {
        $rpcMessageValidationResult = $this->inputValidatorController->validateData(
            $this->rpcMessageValidator,
            $jsonMessage
        );

        if (!$rpcMessageValidationResult->inputValidatedData) {
            $this->onProtocolError($server, $frame, $rpcMessageValidationResult->getErrorMessage());

            return;
        }

        $payloadConstraintResult = $this
            ->getFrameController($frame)
            ->onRPCMessage($rpcMessageValidationResult->inputValidatedData)
        ;

        if (!$payloadConstraintResult->status->isValid()) {
            $this->onProtocolError(
                $server,
                $frame,
                (string) new ConstraintResultErrorMessage($payloadConstraintResult),
            );
        }
    }

    private function onProtocolError(Server $server, Frame $frame, string $reason): void
    {
        $this->logger->debug(sprintf('WebSocket Protocol Error: %s', $reason));
        $server->disconnect($frame->fd, SWOOLE_WEBSOCKET_CLOSE_PROTOCOL_ERROR, $reason);
    }
}
