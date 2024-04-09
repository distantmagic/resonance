<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\BackusNaurFormGrammar;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\BackusNaurFormGrammar;

#[Singleton]
readonly class YesNoGrammar extends BackusNaurFormGrammar
{
    /**
     * @var non-empty-string
     */
    private string $grammar;

    public function __construct()
    {
        $this->grammar = 'root ::= "yes" | "no"';
    }

    public function getGrammarContent(): string
    {
        return $this->grammar;
    }
}
