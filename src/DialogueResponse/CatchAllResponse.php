<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\DialogueResponse;

use Distantmagic\Resonance\DialogueInputInterface;
use Distantmagic\Resonance\DialogueNodeInterface;
use Distantmagic\Resonance\DialogueResponse;
use Distantmagic\Resonance\DialogueResponseResolution;
use Distantmagic\Resonance\DialogueResponseResolutionStatus;

readonly class CatchAllResponse extends DialogueResponse
{
    public function __construct(private DialogueNodeInterface $followUp) {}

    public function getCost(): int
    {
        return 100;
    }

    public function resolveResponse(DialogueInputInterface $dialogueInput): DialogueResponseResolution
    {
        return new DialogueResponseResolution(
            followUp: $this->followUp,
            status: DialogueResponseResolutionStatus::CanRespond,
        );
    }
}
