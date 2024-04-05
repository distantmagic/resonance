<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\BackusNaurFormGrammar\YesNoMaybeGrammar;

#[Singleton]
readonly class LlamaCppExtractYesNoMaybe
{
    public function __construct(
        private LlamaCppClientInterface $llamaCppClient,
        private YesNoMaybeGrammar $yesNoMaybeGrammar,
    ) {}

    public function extract(string $input): YesNoMaybe
    {
        $completion = $this->llamaCppClient->generateCompletion(
            new LlamaCppCompletionRequest(
                backusNaurFormGrammar: $this->yesNoMaybeGrammar,
                llmChatHistory: new LlmChatHistory([
                    new LlmChatMessage(
                        actor: 'system',
                        message: <<<'PROMPT'
                        User will provide the statement.
                        If the statement is affirmand, write "yes".
                        If the statement means that user agrees, write "yes".
                        If the statement means that user wants the thing, write "yes".
                        If the statement is negatory, write "no".
                        If the statement is negative, write "no".
                        If the statement is uncertain, write "maybe".
                        PROMPT
                    ),
                    new LlmChatMessage('user', $input),
                ]),
            ),
        );

        $ret = '';

        foreach ($completion as $token) {
            $ret .= $token;
        }

        return YesNoMaybe::from($ret);
    }
}
