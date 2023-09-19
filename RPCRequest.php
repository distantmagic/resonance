<?php

declare(strict_types=1);

namespace Resonance;

use App\RPCMethod;

/**
 * @template TPayload
 */
readonly class RPCRequest
{
    /**
     * @param TPayload $payload
     */
    public function __construct(
        public RPCMethod $method,
        public mixed $payload,
        public string $requestId,
    ) {}
}
