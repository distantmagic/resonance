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
            $responder->onClose(
                $this->webSocketAuthResolution,
                $this->webSocketConnection,
            );
        }
    }

    public function onRPCMessage(RPCMessage $rpcMessage): void
    {
        $responder = $this
            ->webSocketRPCResponderAggregate
            ->selectResponder($rpcMessage)
        ;

        $this->activeResponders->add($responder);

        $responder->respond(
            $this->webSocketAuthResolution,
            $this->webSocketConnection,
            $rpcMessage
        );
    }
}
