<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Map;

readonly class InputValidatorCollection
{
    /**
     * @var Map<class-string,InputValidator> $inputValidators
     */
    public Map $inputValidators;

    public function __construct()
    {
        $this->inputValidators = new Map();
    }
}
