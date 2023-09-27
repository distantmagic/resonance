<?php

declare(strict_types=1);

namespace Resonance\Event;

use Resonance\Event;
use Throwable;

final readonly class UnhandledException extends Event
{
    public function __construct(public Throwable $throwable) {}
}