<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface DialogueResponseDiscriminatorInterface
{
    /**
     * @param iterable<DialogueResponseInterface> $responses
     */
    public function discriminate(
        iterable $responses,
        DialogueInputInterface $dialogueInput,
    ): ?DialogueNodeInterface;
}
