<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Stringable;

/**
 * @psalm-suppress PossiblyUnusedProperty
 *
 * @template TPayload
 */
readonly class JsonRPCNotification implements Stringable
{
    /**
     * @param TPayload $payload
     */
    public function __construct(
        public JsonRPCMethodInterface $method,
        public mixed $payload,
    ) {}

    public function __toString(): string
    {
        return json_encode([
            $this->method->getValue(),
            $this->payload,
            null,
        ]);
    }
}
