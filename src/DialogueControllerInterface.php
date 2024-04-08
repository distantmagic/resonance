<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface DialogueControllerInterface
{
    public function getRootDialogueNode(): DialogueNodeInterface;
}
