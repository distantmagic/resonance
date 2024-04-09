<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\BackusNaurFormGrammar\YesNoMaybeGrammar;
use Distantmagic\Resonance\LlmPersona\HelpfulAssistant;

#[Singleton(provides: LlamaCppExtractWhenInterface::class)]
readonly class LlamaCppExtractWhen implements LlamaCppExtractWhenInterface
{
    public function __construct(
        private LlamaCppClientInterface $llamaCppClient,
        private YesNoMaybeGrammar $yesNoMaybeGrammar,
    ) {}

    public function extract(
        string $input,
        string $condition,
        LlmPersonaInterface $persona = new HelpfulAssistant(),
    ): LlamaCppExtractYesNoMaybeResult {
        $completion = $this->llamaCppClient->generateCompletion(
            new LlamaCppCompletionRequest(
                backusNaurFormGrammar: $this->yesNoMaybeGrammar,
                llmChatHistory: new LlmChatHistory([
                    new LlmChatMessage('system', $persona->getPersonaDescription()),
                    new LlmChatMessage(
                        actor: 'system',
                        message: <<<'PROMPT'
                        User will provide the statement and the condition.

                        If the condition is explicitly true about the provided statement, write "yes".
                        If the condition is untrue about the provided statement, write "no".
                        If the condition is not related to the the statement, write "no".
                        If you are not certain, write "maybe".
                        PROMPT
                    ),
                    new LlmChatMessage('user', sprintf("Statement:\n%s", $input)),
                    new LlmChatMessage('user', sprintf("Condition:\n%s", $condition)),
                ]),
            ),
        );

        $ret = '';

        foreach ($completion as $token) {
            if ($token->isFailed) {
                return new LlamaCppExtractYesNoMaybeResult(
                    result: null,
                    isFailed: true,
                );
            }

            $ret .= $token;
        }

        return new LlamaCppExtractYesNoMaybeResult(
            result: YesNoMaybe::from($ret),
            isFailed: false,
        );
    }
}
