<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface TickTimerJobInterface extends RegisterableInterface
{
    public function onTimerTick(): void;
}
