<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class DialogueResponseResolution implements DialogueResponseResolutionInterface
{
    public function __construct(
        private DialogueResponseResolutionStatus $status,
    ) {}

    public function getStatus(): DialogueResponseResolutionStatus
    {
        return $this->status;
    }
}
