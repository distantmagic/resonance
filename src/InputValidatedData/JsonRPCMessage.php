<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\InputValidatedData;

use Distantmagic\Resonance\InputValidatedData;
use Distantmagic\Resonance\JsonRPCMethodInterface;

/**
 * @template TPayload
 */
readonly class JsonRPCMessage extends InputValidatedData
{
    /**
     * @param TPayload $payload
     */
    public function __construct(
        public JsonRPCMethodInterface $method,
        public mixed $payload,
        public ?string $requestId,
    ) {}
}
