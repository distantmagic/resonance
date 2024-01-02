<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Set;

readonly class CronJobAggregate
{
    /**
     * @var Set<CronRegisteredJob>
     */
    public Set $cronJobs;

    public function __construct()
    {
        $this->cronJobs = new Set();
    }
}
