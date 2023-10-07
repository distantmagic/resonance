<?php

declare(strict_types=1);

namespace Resonance;

interface RegisterableInterface
{
    public function shouldRegister(): bool;
}
