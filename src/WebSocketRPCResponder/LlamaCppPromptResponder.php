<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\WebSocketRPCResponder;

use Distantmagic\Resonance\BackusNaurFormGrammar\SubjectActionGrammar;
use Distantmagic\Resonance\LlamaCppClient;
use Distantmagic\Resonance\LlamaCppCompletionIterator;
use Distantmagic\Resonance\LlamaCppCompletionRequest;
use Distantmagic\Resonance\LlmPromptTemplate;
use Distantmagic\Resonance\LlmSystemPrompt\SubjectActionSystemPrompt;
use Distantmagic\Resonance\PromptSubjectResponderAggregate;
use Distantmagic\Resonance\RPCNotification;
use Distantmagic\Resonance\WebSocketAuthResolution;
use Distantmagic\Resonance\WebSocketConnection;
use Distantmagic\Resonance\WebSocketRPCResponder;
use Psr\Log\LoggerInterface;
use WeakMap;

/**
 * @template TPayload
 *
 * @template-extends WebSocketRPCResponder<TPayload>
 */
abstract readonly class LlamaCppPromptResponder extends WebSocketRPCResponder
{
    /**
     * @var WeakMap<WebSocketConnection,LlamaCppCompletionIterator>
     */
    private WeakMap $runningCompletions;

    abstract protected function onResponseChunk(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        mixed $responseChunk,
    ): void;

    /**
     * @param TPayload $payload
     */
    abstract protected function toPromptTemplate(mixed $payload): LlmPromptTemplate;

    public function __construct(
        private LlamaCppClient $llamaCppClient,
        private LoggerInterface $logger,
        private PromptSubjectResponderAggregate $promptSubjectResponderAggregate,
        private SubjectActionGrammar $subjectActionGrammar,
        private SubjectActionSystemPrompt $subjectActionSystemPrompt,
    ) {
        /**
         * @var WeakMap<WebSocketConnection,LlamaCppCompletionIterator>
         */
        $this->runningCompletions = new WeakMap();
    }

    public function onBeforeMessage(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
    ): void {
        if ($this->runningCompletions->offsetExists($webSocketConnection)) {
            $this->runningCompletions->offsetGet($webSocketConnection)->stop();
        }
    }

    public function onNotification(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        RPCNotification $rpcNotification,
    ): void {
        $request = new LlamaCppCompletionRequest(
            backusNaurFormGrammar: $this->subjectActionGrammar,
            llmSystemPrompt: $this->subjectActionSystemPrompt,
            promptTemplate: $this->toPromptTemplate($rpcNotification->payload),
        );

        $completion = $this->llamaCppClient->generateCompletion($request);

        $this->runningCompletions->offsetSet($webSocketConnection, $completion);

        $response = $this
            ->promptSubjectResponderAggregate
            ->createResponseFromTokens(
                authenticatedUser: $webSocketAuthResolution->authenticatedUser,
                completion: $completion,
                timeout: 1.0,
            )
        ;

        /**
         * @var mixed $responseChunk explicitly mixed for typechecks
         */
        foreach ($response as $responseChunk) {
            $this->onResponseChunk($webSocketAuthResolution, $webSocketConnection, $responseChunk);
        }
    }
}
