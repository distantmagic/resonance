<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\DialogueMessageProducer;

use Distantmagic\Resonance\DialogueMessageChunk;
use Distantmagic\Resonance\DialogueMessageProducer;
use Distantmagic\Resonance\LlamaCppClientInterface;
use Distantmagic\Resonance\LlamaCppCompletionRequest;
use Generator;

readonly class LlamaCppPromptMessageProducer extends DialogueMessageProducer
{
    public function __construct(
        private LlamaCppClientInterface $llamaCppClient,
        private LlamaCppCompletionRequest $request,
    ) {}

    /**
     * @return Generator<DialogueMessageChunk>
     */
    public function getIterator(): Generator
    {
        $completion = $this->llamaCppClient->generateCompletion(
            request: $this->request,
        );

        foreach ($completion as $token) {
            yield new DialogueMessageChunk(
                content: (string) $token,
                isFailed: $token->isFailed(),
                isLastToken: $token->isLastToken(),
            );
        }
    }
}
