<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\BackusNaurFormGrammar;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\BackusNaurFormGrammar;

#[Singleton]
readonly class YesNoMaybeGrammar extends BackusNaurFormGrammar
{
    /**
     * @var non-empty-string
     */
    private string $grammar;

    public function __construct()
    {
        $this->grammar = 'root ::= "yes" | "no" | "maybe"';
    }

    public function getGrammarContent(): string
    {
        return $this->grammar;
    }
}
