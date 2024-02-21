<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface SideEffectProviderInterface extends RegisterableInterface
{
    public function provideSideEffect(): void;
}
