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
        public JsonSchemaValidator $jsonSchemaValidator,
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

    public function onRPCMessage(RPCMessage $rpcMessage): JsonSchemaValidationResult
    {
        $responder = $this
            ->webSocketRPCResponderAggregate
            ->selectResponder($rpcMessage)
        ;

        $jsonSchemaValidationResult = $this
            ->jsonSchemaValidator
            ->validate($responder, $rpcMessage->payload)
        ;

        if (!empty($jsonSchemaValidationResult->errors)) {
            return $jsonSchemaValidationResult;
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
                    $jsonSchemaValidationResult->data,
                    $rpcMessage->requestId,
                ),
            );
        } else {
            $responder->onNotification(
                $this->webSocketAuthResolution,
                $this->webSocketConnection,
                new RPCNotification(
                    $rpcMessage->method,
                    $jsonSchemaValidationResult->data,
                )
            );
        }

        return $jsonSchemaValidationResult;
    }
}
