<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface ConstraintSourceInterface
{
    public function getConstraint(): Constraint;
}
