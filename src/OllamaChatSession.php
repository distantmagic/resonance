<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Set;
use Generator;

readonly class OllamaChatSession
{
    /**
     * @var Set<OllamaChatMessage>
     */
    private Set $messages;

    public function __construct(
        public string $model,
        public OllamaClient $ollamaClient,
    ) {
        $this->messages = new Set();
    }

    /**
     * @return Generator<OllamaChatToken>
     */
    public function respond(string $userMessageContent): Generator
    {
        $this
            ->messages
            ->add(new OllamaChatMessage($userMessageContent, OllamaChatRole::User))
        ;

        $chatRequest = new OllamaChatRequest(
            model: $this->model,
            messages: $this->messages->toArray(),
        );

        yield from $this->ollamaClient->generateChatCompletion($chatRequest);
    }
}
