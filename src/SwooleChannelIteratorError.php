<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class SwooleChannelIteratorError
{
    public function __construct(
        public bool $isTimeout,
    ) {}
}
