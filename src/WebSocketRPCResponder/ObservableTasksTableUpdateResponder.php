<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\WebSocketRPCResponder;

use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\Constraint\ObjectConstraint;
use Distantmagic\Resonance\LlamaCppCompletionIterator;
use Distantmagic\Resonance\RPCNotification;
use Distantmagic\Resonance\RPCRequest;
use Distantmagic\Resonance\WebSocketAuthResolution;
use Distantmagic\Resonance\WebSocketConnection;
use Distantmagic\Resonance\WebSocketRPCResponder;
use Psr\Log\LoggerInterface;
use RuntimeException;
use WeakMap;

/**
 * @template TPayload
 *
 * @template-extends WebSocketRPCResponder<TPayload>
 */
readonly class ObservableTasksTableUpdateResponder extends WebSocketRPCResponder
{
    /**
     * @var WeakMap<WebSocketConnection,LlamaCppCompletionIterator>
     */
    private WeakMap $runningCompletions;

    public function __construct(
        private LoggerInterface $logger,
    ) {
        /**
         * @var WeakMap<WebSocketConnection,LlamaCppCompletionIterator>
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
            $this->runningCompletions->offsetGet($webSocketConnection)->stop();
        }
    }

    public function onNotification(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        RPCNotification $rpcNotification,
    ): never {
        throw new RuntimeException('Unexpected notification');
    }

    public function onRequest(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        RPCRequest $rpcRequest,
    ): void {}
}
