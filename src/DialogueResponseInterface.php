<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Generator;

interface DialogueResponseInterface
{
    public function getCost(): int;

    public function resolveResponse(DialogueInputInterface $dialogueInput): DialogueResponseResolutionInterface;

    /**
     * @return Generator<mixed,LlmCompletionProgressInterface,mixed,DialogueResponseResolutionInterface>
     */
    public function resolveResponseWithProgress(DialogueInputInterface $dialogueInput): Generator;
}
