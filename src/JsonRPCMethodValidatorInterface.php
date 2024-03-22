<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface JsonRPCMethodValidatorInterface
{
    /**
     * @return array<JsonRPCMethodInterface>
     */
    public function cases(): array;

    public function castToRPCMethod(string $methodName): JsonRPCMethodInterface;

    /**
     * @return array<string>
     */
    public function values(): array;
}
