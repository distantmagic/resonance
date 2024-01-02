<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Event;

use Distantmagic\Resonance\Event;

final readonly class HttpServerBeforeStop extends Event
{
    public function __construct() {}
}
