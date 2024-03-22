<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\WebSocketJsonRPCResponder;

use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\Constraint\ObjectConstraint;
use Distantmagic\Resonance\JsonRPCRequest;
use Distantmagic\Resonance\JsonRPCResponse;
use Distantmagic\Resonance\ObservableTaskSlotStatusUpdate;
use Distantmagic\Resonance\ObservableTaskTable;
use Distantmagic\Resonance\WebSocketAuthResolution;
use Distantmagic\Resonance\WebSocketConnection;
use Distantmagic\Resonance\WebSocketJsonRPCResponder;

/**
 * @template TPayload
 *
 * @template-extends WebSocketJsonRPCResponder<TPayload>
 */
readonly class ObservableTasksTableUpdateResponder extends WebSocketJsonRPCResponder
{
    public function __construct(
        private ObservableTaskTable $observableTaskTable,
    ) {}

    public function getConstraint(): Constraint
    {
        return new ObjectConstraint();
    }

    public function onRequest(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        JsonRPCRequest $rpcRequest,
    ): void {
        $this->observableTaskTable->observers->add(
            static function (ObservableTaskSlotStatusUpdate $observableTaskSlotStatusUpdate) use (
                $rpcRequest,
                $webSocketConnection,
            ): bool {
                if (!$webSocketConnection->status->isOpen()) {
                    return false;
                }

                return $webSocketConnection->push(new JsonRPCResponse(
                    rpcRequest: $rpcRequest,
                    content: $observableTaskSlotStatusUpdate,
                ));
            }
        );
    }
}
