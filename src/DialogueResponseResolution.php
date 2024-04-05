<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class DialogueResponseResolution implements DialogueResponseResolutionInterface
{
    public function __construct(
        private ?DialogueNodeInterface $followUp,
        private DialogueResponseResolutionStatus $status,
    ) {}

    public function getFollowUp(): ?DialogueNodeInterface
    {
        return $this->followUp;
    }

    public function getStatus(): DialogueResponseResolutionStatus
    {
        return $this->status;
    }
}
