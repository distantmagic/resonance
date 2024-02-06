<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TCastedData of InputValidatedData
 * @template TValidatedData
 *
 * @template-implements CastsValidatedDataInterface<TCastedData,TValidatedData>
 */
abstract readonly class InputValidator implements CastsValidatedDataInterface, ConstraintSourceInterface {}
