<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface RegisterableInterface
{
    public function shouldRegister(): bool;
}
