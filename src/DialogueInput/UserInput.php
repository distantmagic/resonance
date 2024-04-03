<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\DialogueInput;

use Distantmagic\Resonance\DialogueInputInterface;

readonly class UserInput implements DialogueInputInterface
{
    public function __construct(
        private string $content,
    ) {}

    public function getContent(): string
    {
        return $this->content;
    }
}
