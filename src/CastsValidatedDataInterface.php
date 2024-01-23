<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TCastedData of InputValidatedData
 * @template TValidatedData
 */
interface CastsValidatedDataInterface
{
    /**
     * @param TValidatedData $data
     *
     * @return TCastedData
     */
    public function castValidatedData(mixed $data): InputValidatedData;
}
