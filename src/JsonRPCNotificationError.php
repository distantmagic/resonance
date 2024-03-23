<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Stringable;

/**
 * @psalm-suppress PossiblyUnusedProperty
 *
 * @template TPayload
 */
readonly class JsonRPCNotificationError implements Stringable
{
    /**
     * @param TPayload $payload
     */
    public function __construct(
        public JsonRPCMethodInterface $method,
        public int $code = -32000,
        public string $message = 'Server error',
        public mixed $payload = null,
    ) {}

    public function __toString(): string
    {
        return json_encode([
            'jsonrpc' => '2.0',
            'method' => $this->method->getValue(),
            'error' => [
                'code' => $this->code,
                'data' => $this->payload,
                'message' => $this->message,
            ],
        ]);
    }
}
