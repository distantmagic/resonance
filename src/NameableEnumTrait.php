<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

trait NameableEnumTrait
{
    /**
     * @return non-empty-string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
