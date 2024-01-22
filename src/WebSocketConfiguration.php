<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use SensitiveParameter;

readonly class WebSocketConfiguration
{
    /**
     * @psalm-taint-source system_secret $maxConnections
     */
    public function __construct(
        #[SensitiveParameter]
        public int $maxConnections,
    ) {}
}
