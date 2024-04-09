<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\DialogueResponse;

use Closure;
use Distantmagic\Resonance\DialogueInputInterface;
use Distantmagic\Resonance\DialogueResponse;
use Distantmagic\Resonance\DialogueResponseResolution;
use Distantmagic\Resonance\DialogueResponseResolutionStatus;
use Distantmagic\Resonance\LlamaCppExtractWhenInterface;
use Distantmagic\Resonance\YesNoMaybe;

readonly class LlamaCppExtractWhenResponse extends DialogueResponse
{
    /**
     * @var Closure(YesNoMaybe):DialogueResponseResolution $whenProvided
     */
    private Closure $whenProvided;

    /**
     * @param callable(YesNoMaybe):DialogueResponseResolution $whenProvided
     */
    public function __construct(
        private LlamaCppExtractWhenInterface $llamaCppExtractWhen,
        private string $condition,
        callable $whenProvided,
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
        );

        if ($extracted->isFailed || is_null($extracted->result)) {
            return new DialogueResponseResolution(
                followUp: null,
                status: DialogueResponseResolutionStatus::Failed,
            );
        }

        return ($this->whenProvided)($extracted->result);
    }
}
