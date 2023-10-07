<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface RPCMethodValidatorInterface
{
    /**
     * @return array<RPCMethodInterface>
     */
    public function cases(): array;

    public function castToRPCMethod(string $methodName): RPCMethodInterface;

    /**
     * @return array<string>
     */
    public function names(): array;
}
