<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface NameableInterface
{
    /**
     * @return non-empty-string
     */
    public function getName(): string;
}
