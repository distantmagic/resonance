<?php

declare(strict_types=1);

namespace Resonance;

abstract readonly class CrudActionGate
{
    abstract public function can(CrudAction $crudAction): bool;
}
