<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface LoggableInterface
{
    public function shouldLog(): bool;
}
