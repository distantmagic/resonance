<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\DialogueResponseCondition;

use Distantmagic\Resonance\DialogueInputInterface;
use Distantmagic\Resonance\DialogueResponseCondition;

readonly class ExactInputCondition extends DialogueResponseCondition
{
    public function __construct(
        public string $content,
    ) {}

    public function getCost(): int
    {
        return 2;
    }

    public function isMetBy(DialogueInputInterface $dialogueInput): bool
    {
        return $dialogueInput->getContent() === $this->content;
    }
}
