<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;

readonly class WebSocketAwareCollection
{
    /**
     * @var Map<class-string<WebSocketAwareInterface>,WebSocketAwareInterface> $webSocketAwares
     */
    public Map $webSocketAwares;

    public function __construct()
    {
        $this->webSocketAwares = new Map();
    }
}
