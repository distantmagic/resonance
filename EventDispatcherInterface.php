<?php

declare(strict_types=1);

namespace Resonance;

interface EventDispatcherInterface
{
    public function dispatch(EventInterface $event): void;
}
