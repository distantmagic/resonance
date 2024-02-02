<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Event;

use Distantmagic\Resonance\Event;
use Swoole\Server;

/**
 * @psalm-suppress PossiblyUnusedProperty used in listeners
 */
final readonly class HttpServerStarted extends Event
{
    public function __construct(public Server $server) {}
}
