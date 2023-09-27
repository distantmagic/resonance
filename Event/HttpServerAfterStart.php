<?php

declare(strict_types=1);

namespace Resonance\Event;

use Resonance\Event;

final readonly class HttpServerAfterStart extends Event
{
    public function __construct() {}
}
