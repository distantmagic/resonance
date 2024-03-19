<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\WebSocketRPCResponder;

use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\Constraint\ObjectConstraint;
use Distantmagic\Resonance\ObservableTaskTable;
use Distantmagic\Resonance\ObservableTaskTableSlotStatusUpdateIterator;
use Distantmagic\Resonance\RPCRequest;
use Distantmagic\Resonance\RPCResponse;
use Distantmagic\Resonance\WebSocketAuthResolution;
use Distantmagic\Resonance\WebSocketConnection;
use Distantmagic\Resonance\WebSocketRPCResponder;
use Psr\Log\LoggerInterface;
use WeakMap;

/**
 * @template TPayload
 *
 * @template-extends WebSocketRPCResponder<TPayload>
 */
readonly class ObservableTasksTableUpdateResponder extends WebSocketRPCResponder
{
    /**
     * @var WeakMap<WebSocketConnection,ObservableTaskTableSlotStatusUpdateIterator>
     */
    private WeakMap $runningCompletions;

    public function __construct(
        private LoggerInterface $logger,
        private ObservableTaskTable $observableTaskTable,
    ) {
        /**
         * @var WeakMap<WebSocketConnection,ObservableTaskTableSlotStatusUpdateIterator>
         */
        $this->runningCompletions = new WeakMap();
    }

    public function getConstraint(): Constraint
    {
        return new ObjectConstraint();
    }

    public function onBeforeMessage(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
    ): void {
        if ($this->runningCompletions->offsetExists($webSocketConnection)) {
            $this->runningCompletions->offsetGet($webSocketConnection)->close();
        }
    }

    public function onRequest(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        RPCRequest $rpcRequest,
    ): void {
        $observableTaskUpdatesIterator = new ObservableTaskTableSlotStatusUpdateIterator($this->observableTaskTable);

        $this->runningCompletions->offsetSet($webSocketConnection, $observableTaskUpdatesIterator);

        foreach ($observableTaskUpdatesIterator as $observableTaskSlotStatusUpdate) {
            if (!$webSocketConnection->status->isOpen()) {
                break;
            }

            $webSocketConnection->push(new RPCResponse(
                rpcRequest: $rpcRequest,
                content: $observableTaskSlotStatusUpdate,
            ));
        }
    }
}
