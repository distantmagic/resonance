<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @psalm-suppress PossiblyUnusedProperty used in applications
 *
 * @template TPayload
 */
readonly class JsonRPCRequest
{
    /**
     * @param TPayload $payload
     */
    public function __construct(
        public JsonRPCMethodInterface $method,
        public mixed $payload,
        public string $requestId,
    ) {}
}
