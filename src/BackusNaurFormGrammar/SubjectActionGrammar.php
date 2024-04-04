<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\BackusNaurFormGrammar;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\BackusNaurFormGrammar;
use Distantmagic\Resonance\PromptSubjectResponderCollection;

#[Singleton]
readonly class SubjectActionGrammar extends BackusNaurFormGrammar
{
    /**
     * @var non-empty-string
     */
    private string $grammar;

    public function __construct(
        PromptSubjectResponderCollection $promptSubjectResponderCollection,
    ) {
        /**
         * @var array<non-empty-string> $subjects
         */
        $subjects = ['("unknown" " " "unknown")'];

        foreach ($promptSubjectResponderCollection->getPromptableActions() as $subject => $actions) {
            $subjects[] = sprintf(
                '("%s" " " ("%s"))',
                $subject,
                implode('" | "', $actions->toArray())
            );
        }

        $subjectsSerialized = sprintf('%s', implode(' | ', $subjects));

        $stringGrammar = <<<'STRING_GRAMMAR'
        "\"" (
            [^"\\] |
             "\\" (["\\/bfnrt] | "u" [0-9a-zA-Z] [0-9a-zA-Z] [0-9a-zA-Z] [0-9a-zA-Z])
        )* "\""
        STRING_GRAMMAR;

        $this->grammar = <<<GRAMMAR
        root ::= ({$subjectsSerialized}) " " parameters

        parameters ::= string

        string ::= {$stringGrammar}
        GRAMMAR;
    }

    public function getGrammarContent(): string
    {
        return $this->grammar;
    }
}
