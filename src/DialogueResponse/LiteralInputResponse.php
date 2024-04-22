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

readonly class LiteralInputResponse extends DialogueResponse
{
    public function __construct(
        private string $when,
        private ?DialogueNodeInterface $followUp,
    ) {}

    public function getCost(): int
    {
        return 2;
    }

    public function resolveResponseWithProgress(DialogueInputInterface $dialogueInput): Generator
    {
        yield new LlmCompletionProgress(
            category: 'literal_input_response',
            shouldNotify: false,
        );

        if ($dialogueInput->getContent() === $this->when) {
            return new DialogueResponseResolution(
                followUp: $this->followUp,
                status: DialogueResponseResolutionStatus::CanRespond,
            );
        }

        return new DialogueResponseResolution(
            followUp: null,
            status: DialogueResponseResolutionStatus::CannotRespond,
        );
    }
}
