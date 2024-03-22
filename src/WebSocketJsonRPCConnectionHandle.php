<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\InputValidatedData\JsonRPCMessage;
use Ds\Set;

readonly class WebSocketJsonRPCConnectionHandle
{
    /**
     * @var Set<WebSocketJsonRPCResponderInterface>
     */
    private Set $activeResponders;

    public function __construct(
        public WebSocketJsonRPCResponderAggregate $webSocketJsonRPCResponderAggregate,
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

    public function onRPCMessage(JsonRPCMessage $rpcMessage): ConstraintResult
    {
        $responder = $this
            ->webSocketJsonRPCResponderAggregate
            ->selectResponder($rpcMessage)
        ;

        $constraintResult = $this
            ->webSocketJsonRPCResponderAggregate
            ->cachedConstraints
            ->get($responder)
            ->validate($rpcMessage->payload)
        ;

        if (!$constraintResult->status->isValid()) {
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
                new JsonRPCRequest(
                    $rpcMessage->method,
                    $constraintResult->castedData,
                    $rpcMessage->requestId,
                ),
            );
        } else {
            $responder->onNotification(
                $this->webSocketAuthResolution,
                $this->webSocketConnection,
                new JsonRPCNotification(
                    $rpcMessage->method,
                    $constraintResult->castedData,
                )
            );
        }

        return $constraintResult;
    }
}
