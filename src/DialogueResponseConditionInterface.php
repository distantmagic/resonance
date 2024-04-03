<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface DialogueResponseConditionInterface
{
    public function getCost(): int;

    public function isMetBy(DialogueInputInterface $dialogueInput): bool;
}
