<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Stringable;

readonly class RPCResponse implements Stringable
{
    public function __construct(
        private string $requestId,
        private string|Stringable $content,
    ) {}

    public function __toString(): string
    {
        return json_encode([
            $this->requestId,
            (string) $this->content,
        ]);
    }
}
