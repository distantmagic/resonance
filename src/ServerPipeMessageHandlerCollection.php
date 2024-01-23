<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use Ds\Set;

readonly class ServerPipeMessageHandlerCollection
{
    /**
     * @var Map<class-string<ServerPipeMessageInterface>,Set<ServerPipeMessageHandlerInterface>>
     */
    public Map $serverPipeMessageHandler;

    public function __construct()
    {
        $this->serverPipeMessageHandler = new Map();
    }

    /**
     * @param class-string<ServerPipeMessageInterface> $className
     */
    public function addServerPipeMessageHandler(
        string $className,
        ServerPipeMessageHandlerInterface $serverPipeMessageHandler,
    ): void {
        if (!$this->serverPipeMessageHandler->hasKey($className)) {
            $this->serverPipeMessageHandler->put($className, new Set());
        }

        $this
            ->serverPipeMessageHandler
            ->get($className)
            ->add($serverPipeMessageHandler)
        ;
    }
}
