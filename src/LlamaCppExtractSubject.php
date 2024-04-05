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

    public function extract(
        string $input,
        string $topic,
    ): ?string {
        $completion = $this->llamaCppClient->generateCompletion(
            new LlamaCppCompletionRequest(
                backusNaurFormGrammar: new InlineGrammar('root ::= [0-9a-zA-Z\" ]+'),
                llmChatHistory: new LlmChatHistory([
                    new LlmChatMessage(
                        actor: 'system',
                        message: <<<PROMPT
                        User is about to provide the $topic.
                        If user provides the $topic, repeat only that $topic, without any additional comment.
                        If user did not provide $topic or it is not certain, write the empty string: ""
                        If possible use only nouns to describe the $topic.
                        Respond only with provided $topic.
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
