<?php

declare(strict_types=1);

namespace Resonance;

use App\RPCMethod;

/**
 * @psalm-suppress PossiblyUnusedProperty
 *
 * @template TPayload
 */
readonly class RPCNotification
{
    public function __construct(
        public RPCMethod $method,
        public mixed $payload,
    ) {}
}
