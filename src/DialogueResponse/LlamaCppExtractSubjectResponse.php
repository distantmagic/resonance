<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\DialogueResponse;

use Closure;
use Distantmagic\Resonance\DialogueInputInterface;
use Distantmagic\Resonance\DialogueResponse;
use Distantmagic\Resonance\DialogueResponseResolution;
use Distantmagic\Resonance\DialogueResponseResolutionStatus;
use Distantmagic\Resonance\LlamaCppExtractSubjectInterface;
use Distantmagic\Resonance\LlamaCppExtractSubjectResult;
use Distantmagic\Resonance\LlmPersona\HelpfulAssistant;
use Distantmagic\Resonance\LlmPersonaInterface;

readonly class LlamaCppExtractSubjectResponse extends DialogueResponse
{
    /**
     * @var Closure(LlamaCppExtractSubjectResult):DialogueResponseResolution $whenProvided
     */
    private Closure $whenProvided;

    /**
     * @param callable(LlamaCppExtractSubjectResult):DialogueResponseResolution $whenProvided
     */
    public function __construct(
        private LlamaCppExtractSubjectInterface $llamaCppExtractSubject,
        private string $topic,
        callable $whenProvided,
        private LlmPersonaInterface $persona = new HelpfulAssistant(),
    ) {
        $this->whenProvided = Closure::fromCallable($whenProvided);
    }

    public function getCost(): int
    {
        return 50;
    }

    public function resolveResponse(DialogueInputInterface $dialogueInput): DialogueResponseResolution
    {
        $extracted = $this->llamaCppExtractSubject->extract(
            input: $dialogueInput->getContent(),
            persona: $this->persona,
            topic: $this->topic,
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
