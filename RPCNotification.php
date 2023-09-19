<?php

declare(strict_types=1);

namespace Resonance;

/**
 * @psalm-suppress PossiblyUnusedProperty
 *
 * @template TPayload
 */
readonly class RPCNotification
{
    public function __construct(
        public RPCMethodInterface $method,
        public mixed $payload,
    ) {}
}
