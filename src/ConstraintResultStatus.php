<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum ConstraintResultStatus
{
    case Invalid;
    case Valid;

    public function isValid(): bool
    {
        return ConstraintResultStatus::Valid === $this;
    }
}
