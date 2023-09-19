<?php

declare(strict_types=1);

namespace App\InputValidatedData;

use App\InputValidatedData;
use App\RPCMethod;

/**
 * @template TPayload
 */
readonly class RPCMessage extends InputValidatedData
{
    /**
     * @param TPayload $payload
     */
    public function __construct(
        public RPCMethod $method,
        public mixed $payload,
        public ?string $requestId,
    ) {}
}
