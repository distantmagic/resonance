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
use Distantmagic\Resonance\LlmCompletionProgressInterface;
use Distantmagic\Resonance\LlmPersona\HelpfulAssistant;
use Distantmagic\Resonance\LlmPersonaInterface;
use Generator;

/**
 * @psalm-type TCallbackReturn = DialogueResponseResolution|Generator<mixed,LlmCompletionProgressInterface,mixed,DialogueResponseResolution>
 */
readonly class LlamaCppExtractSubjectResponse extends DialogueResponse
{
    /**
     * @var Closure(LlamaCppExtractSubjectResult):TCallbackReturn $whenProvided
     */
    private Closure $whenProvided;

    /**
     * @param callable(LlamaCppExtractSubjectResult):TCallbackReturn $whenProvided
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

    public function resolveResponseWithProgress(DialogueInputInterface $dialogueInput): Generator
    {
        $extractedGenerator = $this->llamaCppExtractSubject->extractWithProgress(
            input: $dialogueInput->getContent(),
            persona: $this->persona,
            topic: $this->topic,
        );

        yield from $extractedGenerator;

        $extracted = $extractedGenerator->getReturn();

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

        $ret = ($this->whenProvided)($extracted);

        if ($ret instanceof Generator) {
            yield from $ret;

            return $ret->getReturn();
        }

        return $ret;
    }
}
