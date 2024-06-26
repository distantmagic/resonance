<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\BackusNaurFormGrammar\YesNoMaybeGrammar;
use Distantmagic\Resonance\LlmPersona\HelpfulAssistant;
use Generator;

use function Distantmagic\Resonance\helpers\generatorGetReturn;

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
    ): LlamaCppExtractWhenResult {
        $extractGenerator = $this->extractWithProgress($input, $condition, $persona);

        return generatorGetReturn($extractGenerator);
    }

    public function extractWithProgress(
        string $input,
        string $condition,
        LlmPersonaInterface $persona = new HelpfulAssistant(),
    ): Generator {
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
            yield new LlmCompletionProgress(
                category: 'extract_when',
                shouldNotify: true,
            );

            if ($token->isFailed()) {
                return new LlamaCppExtractWhenResult(
                    condition: $condition,
                    isFailed: true,
                    isMatched: false,
                    input: $input,
                    result: YesNoMaybe::No,
                );
            }

            $ret .= $token;
        }

        $yesNoMaybe = YesNoMaybe::tryFrom($ret);

        if (!($yesNoMaybe instanceof YesNoMaybe)) {
            return new LlamaCppExtractWhenResult(
                condition: $condition,
                isFailed: true,
                isMatched: false,
                input: $input,
                result: YesNoMaybe::No,
            );
        }

        return new LlamaCppExtractWhenResult(
            condition: $condition,
            isFailed: false,
            isMatched: true,
            input: $input,
            result: $yesNoMaybe,
        );
    }
}
