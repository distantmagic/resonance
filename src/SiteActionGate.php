<?php

declare(strict_types=1);

namespace Resonance;

abstract readonly class SiteActionGate
{
    abstract public function can(?UserInterface $user): bool;
}
