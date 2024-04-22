<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use function Distantmagic\Resonance\helpers\generatorGetReturn;

abstract readonly class DialogueResponse implements DialogueResponseInterface
{
    public function resolveResponse(DialogueInputInterface $dialogueInput): DialogueResponseResolutionInterface
    {
        $resolveGenerator = $this->resolveResponseWithProgress($dialogueInput);

        return generatorGetReturn($resolveGenerator);
    }
}
