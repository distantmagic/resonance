<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\InputValidatedData\RPCMessage;
use Ds\Set;

readonly class WebSocketRPCConnectionHandle
{
    /**
     * @var Set<WebSocketRPCResponderInterface>
     */
    private Set $activeResponders;

    public function __construct(
        public WebSocketRPCResponderAggregate $webSocketRPCResponderAggregate,
        public WebSocketAuthResolution $webSocketAuthResolution,
        public WebSocketConnection $webSocketConnection,
    ) {
        $this->activeResponders = new Set();
    }

    public function onClose(): void
    {
        foreach ($this->activeResponders as $responder) {
            $responder->onBeforeMessage(
                $this->webSocketAuthResolution,
                $this->webSocketConnection,
            );
            $responder->onClose(
                $this->webSocketAuthResolution,
                $this->webSocketConnection,
            );
        }
    }

    public function onRPCMessage(RPCMessage $rpcMessage): ConstraintResult
    {
        $responder = $this
            ->webSocketRPCResponderAggregate
            ->selectResponder($rpcMessage)
        ;

        $constraintResult = $this
            ->webSocketRPCResponderAggregate
            ->cachedConstraints
            ->get($responder)
            ->validate($rpcMessage->payload)
        ;

        if ($constraintResult->status->isValid()) {
            return $constraintResult;
        }

        $this->activeResponders->add($responder);

        $responder->onBeforeMessage(
            $this->webSocketAuthResolution,
            $this->webSocketConnection,
        );

        if (is_string($rpcMessage->requestId)) {
            $responder->onRequest(
                $this->webSocketAuthResolution,
                $this->webSocketConnection,
                new RPCRequest(
                    $rpcMessage->method,
                    $constraintResult->castedData,
                    $rpcMessage->requestId,
                ),
            );
        } else {
            $responder->onNotification(
                $this->webSocketAuthResolution,
                $this->webSocketConnection,
                new RPCNotification(
                    $rpcMessage->method,
                    $constraintResult->castedData,
                )
            );
        }

        return $constraintResult;
    }
}
