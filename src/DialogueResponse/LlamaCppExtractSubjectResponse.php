<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\DialogueResponse;

use Closure;
use Distantmagic\Resonance\DialogueInputInterface;
use Distantmagic\Resonance\DialogueResponse;
use Distantmagic\Resonance\DialogueResponseResolution;
use Distantmagic\Resonance\DialogueResponseResolutionStatus;
use Distantmagic\Resonance\LlamaCppExtractSubjectInterface;

readonly class LlamaCppExtractSubjectResponse extends DialogueResponse
{
    /**
     * @var Closure(string):DialogueResponseResolution $whenProvided
     */
    private Closure $whenProvided;

    /**
     * @param callable(string):DialogueResponseResolution $whenProvided
     */
    public function __construct(
        private LlamaCppExtractSubjectInterface $llamaCppExtractSubject,
        private string $topic,
        callable $whenProvided,
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
            topic: $this->topic,
        );

        if (is_null($extracted)) {
            return new DialogueResponseResolution(
                followUp: null,
                status: DialogueResponseResolutionStatus::CannotRespond,
            );
        }

        return ($this->whenProvided)($extracted);
    }
}
