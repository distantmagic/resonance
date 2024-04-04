<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\BackusNaurFormGrammar\InlineGrammar;

readonly class LlamaCppExtractString
{
    public function __construct(
        private LlamaCppClientInterface $llamaCppClient,
    ) {}

    public function extract(
        string $input,
        string $subject,
    ): ?string {
        $completion = $this->llamaCppClient->generateCompletion(
            new LlamaCppCompletionRequest(
                backusNaurFormGrammar: new InlineGrammar('root ::= [0-9a-zA-Z\" ]+'),
                llmChatHistory: new LlmChatHistory([
                    new LlmChatMessage(
                        actor: 'system',
                        message: <<<PROMPT
                        User is about to provide the $subject.
                        If user provides the $subject, repeat only that $subject, without any additional comment.
                        If user did not provide $subject or it is not certain, write the empty string: ""
                        Respond only with provided $subject.
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

        $trimmed = trim($ret, ' "');

        if (0 === strlen($trimmed)) {
            return null;
        }

        return $trimmed;
    }
}
