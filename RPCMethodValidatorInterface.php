<?php

declare(strict_types=1);

namespace Resonance;

interface RPCMethodValidatorInterface
{
    /**
     * @return array<string>
     */
    public function cases(): array;

    public function castToRPCMethod(string $methodName): RPCMethodInterface;
}
