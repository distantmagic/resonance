<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use Ds\Set;

readonly class ServerTaskHandlerCollection
{
    /**
     * @var Map<class-string,Set<ServerTaskHandlerInterface>>
     */
    public Map $serverTaskeHandler;

    public function __construct()
    {
        $this->serverTaskeHandler = new Map();
    }

    /**
     * @param class-string $className
     */
    public function addServerTaskHandler(
        string $className,
        ServerTaskHandlerInterface $serverTaskeHandler,
    ): void {
        if (!$this->serverTaskeHandler->hasKey($className)) {
            $this->serverTaskeHandler->put($className, new Set());
        }

        $this
            ->serverTaskeHandler
            ->get($className)
            ->add($serverTaskeHandler)
        ;
    }
}
