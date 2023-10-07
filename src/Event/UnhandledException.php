<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Event;

use Distantmagic\Resonance\Event;
use Throwable;

final readonly class UnhandledException extends Event
{
    public function __construct(public Throwable $throwable) {}
}
