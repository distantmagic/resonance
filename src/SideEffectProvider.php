<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

abstract readonly class SideEffectProvider implements SideEffectProviderInterface
{
    public function shouldRegister(): bool
    {
        return true;
    }
}
