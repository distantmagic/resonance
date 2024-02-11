<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;
use Ds\Set;

readonly class DoctrineEntityListenerCollection
{
    /**
     * @var Map<class-string,Set<object>>
     */
    public Map $listeners;

    public function __construct()
    {
        $this->listeners = new Map();
    }

    /**
     * @param class-string $className
     */
    public function addEntityListener(string $className, object $listener): void
    {
        if (!$this->listeners->hasKey($className)) {
            $this->listeners->put($className, new Set());
        }

        $this->listeners->get($className)->add($listener);
    }
}
