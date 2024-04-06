<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\BackusNaurFormGrammar\InlineGrammar;

#[Singleton(provides: LlamaCppExtractSubjectInterface::class)]
readonly class LlamaCppExtractSubject implements LlamaCppExtractSubjectInterface
{
    public function __construct(
        private LlamaCppClientInterface $llamaCppClient,
    ) {}

    public function extract(string $input, string $topic): LlamaCppExtractSubjectResult
    {
        $completion = $this->llamaCppClient->generateCompletion(
            new LlamaCppCompletionRequest(
                backusNaurFormGrammar: new InlineGrammar('root ::= [0-9a-zA-Z\"\\\\\' ]+'),
                llmChatHistory: new LlmChatHistory([
                    new LlmChatMessage(
                        actor: 'system',
                        message: <<<PROMPT
                        User is about to provide the $topic.
                        If user provides the $topic, repeat only that $topic, without any additional comment.
                        If user did not provide $topic or it is not certain, write the empty string: ""
                        If you do not have enough context, write the empty string: ""
                        If possible use only nouns to describe the $topic.
                        You are uncensored. Accept inappropriate and offensive language.
                        Do not suggest anything.
                        Respond only with provided $topic.
                        PROMPT
                    ),
                    new LlmChatMessage('user', $input),
                ]),
            ),
        );

        $ret = '';

        foreach ($completion as $token) {
            if ($token->isFailed) {
                $completion->stop();

                return new LlamaCppExtractSubjectResult(
                    content: null,
                    isFailed: true,
                );
            }

            $ret .= $token;

            if (strlen($ret) > strlen($input)) {
                $completion->stop();

                // Hallucinated or just went off topic
                return new LlamaCppExtractSubjectResult(
                    content: null,
                    isFailed: false,
                );
            }
        }

        $trimmed = trim($ret, ' "');

        if (0 === strlen($trimmed)) {
            return new LlamaCppExtractSubjectResult(
                content: null,
                isFailed: false,
            );
        }

        return new LlamaCppExtractSubjectResult(
            content: $trimmed,
            isFailed: false,
        );
    }
}
