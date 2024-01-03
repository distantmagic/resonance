<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

abstract readonly class CronJob implements CronJobInterface
{
    public function shouldRegister(): bool
    {
        return true;
    }
}
