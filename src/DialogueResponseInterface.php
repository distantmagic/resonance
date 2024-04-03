<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface DialogueResponseInterface
{
    public function getCondition(): DialogueResponseConditionInterface;

    public function getFollowUp(): DialogueNodeInterface;
}
