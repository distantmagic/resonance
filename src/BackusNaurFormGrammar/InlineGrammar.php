<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\BackusNaurFormGrammar;

use Distantmagic\Resonance\BackusNaurFormGrammar;

readonly class InlineGrammar extends BackusNaurFormGrammar
{
    public function __construct(
        /**
         * @var non-empty-string
         */
        private string $grammar
    ) {}

    public function getGrammarContent(): string
    {
        return $this->grammar;
    }
}
