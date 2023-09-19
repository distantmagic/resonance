<?php

declare(strict_types=1);

namespace Resonance\InputValidatedData;

use App\InputValidatedData;
use App\RPCMethod;

/**
 * @template TPayload
 */
readonly class RPCMessage extends InputValidatedData
{
    public RPCMethod $method;

    /**
     * @param TPayload $payload
     */
    public function __construct(
        string $methodName,
        public mixed $payload,
        public ?string $requestId,
    ) {
        $this->method = RPCMethod::from($methodName);
    }
}
