<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\InputValidatedData;

use Distantmagic\Resonance\InputValidatedData;
use Distantmagic\Resonance\RPCMethodInterface;

/**
 * @template TPayload
 */
readonly class RPCMessage extends InputValidatedData
{
    /**
     * @param TPayload $payload
     */
    public function __construct(
        public RPCMethodInterface $method,
        public mixed $payload,
        public ?string $requestId,
    ) {}
}
