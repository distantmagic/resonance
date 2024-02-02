<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @psalm-suppress PossiblyUnusedProperty used in applications
 *
 * @template TPayload
 */
readonly class RPCRequest
{
    /**
     * @param TPayload $payload
     */
    public function __construct(
        public RPCMethodInterface $method,
        public mixed $payload,
        public string $requestId,
    ) {}
}
