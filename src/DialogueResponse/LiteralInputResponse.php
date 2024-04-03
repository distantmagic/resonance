<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\DialogueResponse;

use Distantmagic\Resonance\DialogueInputInterface;
use Distantmagic\Resonance\DialogueNodeInterface;
use Distantmagic\Resonance\DialogueResponse;
use Distantmagic\Resonance\DialogueResponseResolution;
use Distantmagic\Resonance\DialogueResponseResolutionStatus;

readonly class LiteralInputResponse extends DialogueResponse
{
    public function __construct(
        private string $when,
        private DialogueNodeInterface $followUp,
    ) {}

    public function getCost(): int
    {
        return 2;
    }

    public function resolveResponse(DialogueInputInterface $dialogueInput): DialogueResponseResolution
    {
        if ($dialogueInput->getContent() === $this->when) {
            return new DialogueResponseResolution(
                status: DialogueResponseResolutionStatus::CanRespond,
            );
        }

        return new DialogueResponseResolution(
            status: DialogueResponseResolutionStatus::CannotRespond,
        );
    }
}
