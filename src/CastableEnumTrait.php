<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

trait CastableEnumTrait
{
    public function toConstant(): string
    {
        return $this::class.'::'.$this->getName();
    }
}
