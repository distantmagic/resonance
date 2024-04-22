<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\DialogueResponse;

use Closure;
use Distantmagic\Resonance\DialogueInputInterface;
use Distantmagic\Resonance\DialogueResponse;
use Distantmagic\Resonance\DialogueResponseResolution;
use Distantmagic\Resonance\DialogueResponseResolutionStatus;
use Distantmagic\Resonance\LlamaCppExtractYesNoMaybe;
use Distantmagic\Resonance\LlamaCppExtractYesNoMaybeResult;
use Distantmagic\Resonance\LlmCompletionProgress;
use Distantmagic\Resonance\LlmCompletionProgressInterface;
use Generator;

/**
 * @psalm-type TCallbackReturn = DialogueResponseResolution|Generator<mixed,LlmCompletionProgressInterface,mixed,DialogueResponseResolution>
 */
readonly class LlamaCppExtractYesNoMaybeResponse extends DialogueResponse
{
    /**
     * @var Closure(LlamaCppExtractYesNoMaybeResult):TCallbackReturn $whenProvided
     */
    private Closure $whenProvided;

    /**
     * @param callable(LlamaCppExtractYesNoMaybeResult):TCallbackReturn $whenProvided
     */
    public function __construct(
        private LlamaCppExtractYesNoMaybe $llamaCppExtractYesNoMaybe,
        callable $whenProvided,
    ) {
        $this->whenProvided = Closure::fromCallable($whenProvided);
    }

    public function getCost(): int
    {
        return 50;
    }

    public function resolveResponseWithProgress(DialogueInputInterface $dialogueInput): Generator
    {
        $extracted = $this->llamaCppExtractYesNoMaybe->extract(input: $dialogueInput->getContent());

        yield new LlmCompletionProgress(
            category: 'extract_yes_no_maybe_response',
            shouldNotify: false,
        );

        if (is_null($extracted->result) || $extracted->isFailed) {
            return new DialogueResponseResolution(
                followUp: null,
                status: DialogueResponseResolutionStatus::Failed,
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
