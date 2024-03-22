<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\WebSocketRPCResponder;

use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\Constraint\ObjectConstraint;
use Distantmagic\Resonance\ObservableTaskSlotStatusUpdate;
use Distantmagic\Resonance\ObservableTaskTable;
use Distantmagic\Resonance\RPCRequest;
use Distantmagic\Resonance\RPCResponse;
use Distantmagic\Resonance\WebSocketAuthResolution;
use Distantmagic\Resonance\WebSocketConnection;
use Distantmagic\Resonance\WebSocketRPCResponder;

/**
 * @template TPayload
 *
 * @template-extends WebSocketRPCResponder<TPayload>
 */
readonly class ObservableTasksTableUpdateResponder extends WebSocketRPCResponder
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
        RPCRequest $rpcRequest,
    ): void {
        $this->observableTaskTable->observers->add(
            static function (ObservableTaskSlotStatusUpdate $observableTaskSlotStatusUpdate) use (
                $rpcRequest,
                $webSocketConnection,
            ): bool {
                if (!$webSocketConnection->status->isOpen()) {
                    return false;
                }

                return $webSocketConnection->push(new RPCResponse(
                    rpcRequest: $rpcRequest,
                    content: $observableTaskSlotStatusUpdate,
                ));
            }
        );
    }
}
