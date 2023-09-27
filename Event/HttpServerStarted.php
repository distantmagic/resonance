<?php

declare(strict_types=1);

namespace Resonance\Event;

use Resonance\Event;

final readonly class HttpServerStarted extends Event
{
    public function __construct() {}
}
