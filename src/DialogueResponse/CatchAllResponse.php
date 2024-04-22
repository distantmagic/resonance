<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\DialogueResponse;

use Distantmagic\Resonance\DialogueInputInterface;
use Distantmagic\Resonance\DialogueNodeInterface;
use Distantmagic\Resonance\DialogueResponse;
use Distantmagic\Resonance\DialogueResponseResolution;
use Distantmagic\Resonance\DialogueResponseResolutionStatus;
use Distantmagic\Resonance\LlmCompletionProgress;
use Generator;

readonly class CatchAllResponse extends DialogueResponse
{
    public function __construct(private DialogueNodeInterface $followUp) {}

    public function getCost(): int
    {
        return 100;
    }

    public function resolveResponseWithProgress(DialogueInputInterface $dialogueInput): Generator
    {
        yield new LlmCompletionProgress(
            category: 'catch_all_response',
            shouldNotify: false,
        );

        return new DialogueResponseResolution(
            followUp: $this->followUp,
            status: DialogueResponseResolutionStatus::CanRespond,
        );
    }
}
