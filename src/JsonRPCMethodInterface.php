<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface JsonRPCMethodInterface
{
    public function getValue(): string;
}
