<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Stringable;

/**
 * @psalm-suppress PossiblyUnusedProperty
 *
 * @template TPayload
 */
readonly class JsonRPCResponseError implements Stringable
{
    /**
     * @param TPayload $payload
     */
    public function __construct(
        private JsonRPCRequest $rpcRequest,
        public int $code = -32000,
        public string $message = 'Server error',
        public mixed $payload = null,
    ) {}

    public function __toString(): string
    {
        return json_encode([
            'id' => $this->rpcRequest->requestId,
            'jsonrpc' => '2.0',
            'method' => $this->rpcRequest->method->getValue(),
            'error' => [
                'code' => $this->code,
                'data' => $this->payload,
                'message' => $this->message,
            ],
        ]);
    }
}
