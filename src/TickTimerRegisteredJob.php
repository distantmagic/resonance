<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class TickTimerRegisteredJob
{
    public function __construct(
        public TickTimerJobInterface $tickTimerJob,
        public int $interval,
    ) {}
}
