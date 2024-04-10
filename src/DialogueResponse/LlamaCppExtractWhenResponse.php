<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\DialogueResponse;

use Closure;
use Distantmagic\Resonance\DialogueInputInterface;
use Distantmagic\Resonance\DialogueResponse;
use Distantmagic\Resonance\DialogueResponseResolution;
use Distantmagic\Resonance\DialogueResponseResolutionStatus;
use Distantmagic\Resonance\LlamaCppExtractWhenInterface;
use Distantmagic\Resonance\LlamaCppExtractWhenResult;
use Distantmagic\Resonance\LlmPersona\HelpfulAssistant;
use Distantmagic\Resonance\LlmPersonaInterface;

readonly class LlamaCppExtractWhenResponse extends DialogueResponse
{
    /**
     * @var Closure(LlamaCppExtractWhenResult):DialogueResponseResolution $whenProvided
     */
    private Closure $whenProvided;

    /**
     * @param callable(LlamaCppExtractWhenResult):DialogueResponseResolution $whenProvided
     */
    public function __construct(
        private LlamaCppExtractWhenInterface $llamaCppExtractWhen,
        private string $condition,
        callable $whenProvided,
        private LlmPersonaInterface $persona = new HelpfulAssistant(),
    ) {
        $this->whenProvided = Closure::fromCallable($whenProvided);
    }

    public function getCost(): int
    {
        return 50;
    }

    public function resolveResponse(
        DialogueInputInterface $dialogueInput
    ): DialogueResponseResolution {
        $extracted = $this->llamaCppExtractWhen->extract(
            condition: $this->condition,
            input: $dialogueInput->getContent(),
            persona: $this->persona,
        );

        if ($extracted->isFailed) {
            return new DialogueResponseResolution(
                followUp: null,
                status: DialogueResponseResolutionStatus::Failed,
            );
        }

        if (!$extracted->isMatched) {
            return new DialogueResponseResolution(
                followUp: null,
                status: DialogueResponseResolutionStatus::CannotRespond,
            );
        }

        return ($this->whenProvided)($extracted);
    }
}
