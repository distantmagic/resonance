<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface DialogueResponseResolutionInterface
{
    public function getFollowUp(): ?DialogueNodeInterface;

    public function getStatus(): DialogueResponseResolutionStatus;
}
