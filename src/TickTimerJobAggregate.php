<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Set;

readonly class TickTimerJobAggregate
{
    /**
     * @var Set<TickTimerRegisteredJob>
     */
    public Set $tickTimerJobs;

    public function __construct()
    {
        $this->tickTimerJobs = new Set();
    }
}
