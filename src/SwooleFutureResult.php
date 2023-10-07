<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class SwooleFutureResult
{
    public function __construct(
        public PromiseState $state,
        public mixed $result,
    ) {}
}
