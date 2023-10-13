<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Event;

use Distantmagic\Resonance\Event;
use Swoole\Http\Request;

final readonly class HttpResponseReady extends Event
{
    public function __construct(public Request $request) {}
}
