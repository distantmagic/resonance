<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

final readonly class DialogueResponseResolution implements DialogueResponseResolutionInterface
{
    public static function cannotRespond(): self
    {
        return new self(
            followUp: null,
            status: DialogueResponseResolutionStatus::CannotRespond,
        );
    }

    public static function canRespond(?DialogueNodeInterface $followUp = null): self
    {
        return new self(
            followUp: $followUp,
            status: DialogueResponseResolutionStatus::CanRespond,
        );
    }

    public static function failed(): self
    {
        return new self(
            followUp: null,
            status: DialogueResponseResolutionStatus::Failed,
        );
    }

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
