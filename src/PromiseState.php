<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum PromiseState
{
    case Fulfilled;
    case Pending;
    case Rejected;
    case Resolving;

    public function isSettled(): bool
    {
        return match ($this) {
            PromiseState::Fulfilled => true,
            PromiseState::Rejected => true,
            default => false,
        };
    }
}
