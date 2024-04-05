<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\DialogueResponse;

use Closure;
use Distantmagic\Resonance\DialogueInputInterface;
use Distantmagic\Resonance\DialogueResponse;
use Distantmagic\Resonance\DialogueResponseResolution;
use Distantmagic\Resonance\DialogueResponseResolutionStatus;
use Distantmagic\Resonance\LlamaCppExtractYesNoMaybe;
use Distantmagic\Resonance\YesNoMaybe;

readonly class LlamaCppExtractYesNoResponse extends DialogueResponse
{
    private Closure $whenProvided;

    /**
     * @param callable(YesNoMaybe):DialogueResponseResolution $whenProvided
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

    public function resolveResponse(DialogueInputInterface $prompt): DialogueResponseResolution
    {
        $extracted = $this->llamaCppExtractYesNoMaybe->extract(input: $prompt->getContent());

        if (!$extracted->isCertain()) {
            return new DialogueResponseResolution(
                followUp: null,
                status: DialogueResponseResolutionStatus::CannotRespond,
            );
        }

        return ($this->whenProvided)($extracted);
    }
}
