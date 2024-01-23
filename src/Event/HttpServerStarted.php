<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Event;

use Distantmagic\Resonance\Event;
use Swoole\Server;

final readonly class HttpServerStarted extends Event
{
    public function __construct(public Server $server) {}
}
