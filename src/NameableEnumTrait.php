<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

trait NameableEnumTrait
{
    public function getName(): string
    {
        return $this->name;
    }
}
