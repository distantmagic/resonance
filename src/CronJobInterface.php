<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface CronJobInterface
{
    public function onCronTick(): void;
}
