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

readonly class LlamaCppExtractYesNoMaybeResponse extends DialogueResponse
{
    /**
     * @var Closure(LlamaCppExtractYesNoMaybeResult):DialogueResponseResolution $whenProvided
     */
    private Closure $whenProvided;

    /**
     * @param callable(LlamaCppExtractYesNoMaybeResult):DialogueResponseResolution $whenProvided
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

    public function resolveResponse(DialogueInputInterface $dialogueInput): DialogueResponseResolution
    {
        $extracted = $this->llamaCppExtractYesNoMaybe->extract(input: $dialogueInput->getContent());

        if (is_null($extracted->result) || $extracted->isFailed) {
            return new DialogueResponseResolution(
                followUp: null,
                status: DialogueResponseResolutionStatus::Failed,
            );
        }

        return ($this->whenProvided)($extracted);
    }
}
