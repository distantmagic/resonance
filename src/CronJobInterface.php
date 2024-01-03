<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface CronJobInterface extends RegisterableInterface
{
    public function onCronTick(): void;
}
