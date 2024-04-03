<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface DialogueResponseInterface
{
    public function getCost(): int;

    public function resolveResponse(DialogueInputInterface $dialogueInput): DialogueResponseResolutionInterface;
}
