<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\DialogueResponseCondition;

use Distantmagic\Resonance\DialogueInputInterface;
use Distantmagic\Resonance\DialogueResponseCondition;
use Distantmagic\Resonance\LlamaCppClientInterface;

readonly class LlamaCppInputCondition extends DialogueResponseCondition
{
    public function __construct(
        public LlamaCppClientInterface $llamaCppClient,
        public string $content,
    ) {}

    public function getCost(): int
    {
        return 50;
    }

    public function isMetBy(DialogueInputInterface $dialogueInput): bool {}
}
