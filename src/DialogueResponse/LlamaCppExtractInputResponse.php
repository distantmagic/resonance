<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\DialogueResponse;

use Distantmagic\Resonance\DialogueInputInterface;
use Distantmagic\Resonance\DialogueResponse;
use Distantmagic\Resonance\DialogueResponseResolution;

readonly class LlamaCppExtractInputResponse extends DialogueResponse
{
    public function getCost(): int
    {
        return 50;
    }

    public function resolveResponse(DialogueInputInterface $prompt): DialogueResponseResolution {}
}
