<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\BackusNaurFormGrammar\InlineGrammar;
use Distantmagic\Resonance\LlmPersona\HelpfulAssistant;
use Generator;

use function Distantmagic\Resonance\helpers\generatorGetReturn;

#[Singleton(provides: LlamaCppExtractSubjectInterface::class)]
readonly class LlamaCppExtractSubject implements LlamaCppExtractSubjectInterface
{
    public function __construct(
        private LlamaCppClientInterface $llamaCppClient,
    ) {}

    public function extract(
        string $input,
        string $topic,
        LlmPersonaInterface $persona = new HelpfulAssistant(),
    ): LlamaCppExtractSubjectResult {
        $extractGenerator = $this->extractWithProgress($input, $topic, $persona);

        return generatorGetReturn($extractGenerator);
    }

    public function extractWithProgress(
        string $input,
        string $topic,
        LlmPersonaInterface $persona = new HelpfulAssistant(),
    ): Generator {
        $completion = $this->llamaCppClient->generateCompletion(
            new LlamaCppCompletionRequest(
                backusNaurFormGrammar: new InlineGrammar('root ::= [0-9a-zA-Z\"\\\\\' ]+'),
                llmChatHistory: new LlmChatHistory([
                    new LlmChatMessage('system', $persona->getPersonaDescription()),
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
            yield new LlmCompletionProgress(
                category: 'extract_subject',
                shouldNotify: true,
            );

            if ($token->isFailed()) {
                return new LlamaCppExtractSubjectResult(
                    content: '',
                    input: $input,
                    isFailed: true,
                    isMatched: false,
                    topic: $topic,
                );
            }

            $ret .= $token;

            if (strlen($ret) > strlen($input)) {
                $completion->stop();

                // Hallucinated or just went off topic
                return new LlamaCppExtractSubjectResult(
                    content: '',
                    input: $input,
                    isFailed: false,
                    isMatched: false,
                    topic: $topic,
                );
            }
        }

        $trimmed = trim($ret, ' "');

        if (0 === strlen($trimmed) || !str_contains($input, $trimmed)) {
            return new LlamaCppExtractSubjectResult(
                content: '',
                input: $input,
                isFailed: false,
                isMatched: false,
                topic: $topic,
            );
        }

        return new LlamaCppExtractSubjectResult(
            content: $trimmed,
            input: $input,
            isFailed: false,
            isMatched: true,
            topic: $topic,
        );

    }
}
