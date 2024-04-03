<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

readonly class DialogueResponse implements DialogueResponseInterface
{
    public function __construct(
        private DialogueNodeInterface $followUp,
        private DialogueResponseConditionInterface $when,
    ) {}

    public function getCondition(): DialogueResponseConditionInterface
    {
        return $this->when;
    }

    public function getFollowUp(): DialogueNodeInterface
    {
        return $this->followUp;
    }
}
