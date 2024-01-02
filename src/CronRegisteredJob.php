<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Cron\CronExpression;

readonly class CronRegisteredJob
{
    public function __construct(
        public CronJobInterface $cronJob,
        public CronExpression $expression,
        public string $name,
    ) {}
}
