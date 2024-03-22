<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Stringable;

readonly class JsonRPCResponse implements Stringable
{
    public function __construct(
        private JsonRPCRequest $rpcRequest,
        private mixed $content,
    ) {}

    public function __toString(): string
    {
        return json_encode([
            $this->rpcRequest->method,
            $this->content,
            $this->rpcRequest->requestId,
        ]);
    }
}
