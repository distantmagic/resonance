<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\LlmPromptTemplate\MistralInstructChat;

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
                promptTemplate: new MistralInstructChat(<<<PROMPT
                User is about to provide the $subject.
                If user provides the $subject, repeat only that $subject, without any additional comment.
                If user did not provide $subject or it is not certain, write the empty string: ""

                User input:
                $input
                PROMPT),
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
