<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\WebSocketJsonRPCResponder;

use Distantmagic\Resonance\BackusNaurFormGrammar\SubjectActionGrammar;
use Distantmagic\Resonance\JsonRPCRequest;
use Distantmagic\Resonance\LlamaCppClient;
use Distantmagic\Resonance\LlamaCppCompletionIterator;
use Distantmagic\Resonance\LlamaCppCompletionRequest;
use Distantmagic\Resonance\LlmPrompt\SubjectActionPrompt;
use Distantmagic\Resonance\LlmPromptTemplate;
use Distantmagic\Resonance\LlmPromptTemplate\ChainPrompt;
use Distantmagic\Resonance\ObservableTaskCategory;
use Distantmagic\Resonance\ObservableTaskFactory;
use Distantmagic\Resonance\ObservableTaskStatus;
use Distantmagic\Resonance\ObservableTaskStatusUpdate;
use Distantmagic\Resonance\ObservableTaskTable;
use Distantmagic\Resonance\PromptSubjectResponderAggregate;
use Distantmagic\Resonance\WebSocketAuthResolution;
use Distantmagic\Resonance\WebSocketConnection;
use Distantmagic\Resonance\WebSocketJsonRPCResponder;
use Generator;
use Psr\Log\LoggerInterface;
use WeakMap;

/**
 * @template TPayload
 *
 * @template-extends WebSocketJsonRPCResponder<TPayload>
 */
abstract readonly class LlamaCppSubjectActionPromptResponder extends WebSocketJsonRPCResponder
{
    /**
     * @var WeakMap<WebSocketConnection,LlamaCppCompletionIterator>
     */
    private WeakMap $runningCompletions;

    /**
     * @param TPayload $payload
     */
    abstract protected function getPromptFromPayload(mixed $payload): string;

    abstract protected function onRequestFailure(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        JsonRPCRequest $rpcRequest,
    ): void;

    abstract protected function onResponseChunk(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        JsonRPCRequest $rpcRequest,
        mixed $responseChunk,
        bool $isLastChunk,
    ): void;

    abstract protected function toPromptTemplate(string $prompt): LlmPromptTemplate;

    public function __construct(
        private LlamaCppClient $llamaCppClient,
        private LoggerInterface $logger,
        private ObservableTaskTable $observableTaskTable,
        private PromptSubjectResponderAggregate $promptSubjectResponderAggregate,
        private SubjectActionGrammar $subjectActionGrammar,
        private SubjectActionPrompt $subjectActionPrompt,
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

    public function onRequest(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        JsonRPCRequest $rpcRequest,
    ): void {
        $this->observableTaskTable->observe(ObservableTaskFactory::withTimeout(
            iterableTask: function () use (
                $webSocketAuthResolution,
                $webSocketConnection,
                $rpcRequest,
            ): Generator {
                yield from $this->onObservableRequest(
                    $webSocketAuthResolution,
                    $webSocketConnection,
                    $rpcRequest,
                );
            },
            inactivityTimeout: 5.0,
            name: 'websocket_jsonrpc_response',
            category: ObservableTaskCategory::LlamaCpp->value,
        ));
    }

    /**
     * @param JsonRPCRequest<TPayload> $rpcRequest
     *
     * @return Generator<ObservableTaskStatusUpdate>
     */
    private function onObservableRequest(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        JsonRPCRequest $rpcRequest,
    ): Generator {
        $request = new LlamaCppCompletionRequest(
            backusNaurFormGrammar: $this->subjectActionGrammar,
            promptTemplate: new ChainPrompt([
                $this->toPromptTemplate($this->subjectActionPrompt->getPromptContent()),
                $this->toPromptTemplate($this->getPromptFromPayload($rpcRequest->payload)),
            ]),
        );

        $completion = $this->llamaCppClient->generateCompletion($request);

        $this->runningCompletions->offsetSet($webSocketConnection, $completion);

        $response = $this
            ->promptSubjectResponderAggregate
            ->createResponseFromTokens(
                authenticatedUser: $webSocketAuthResolution->authenticatedUser,
                completion: $completion,
                inactivityTimeout: 1.0,
            )
        ;

        foreach ($response as $responseChunk) {
            if ($responseChunk->isFailed || $responseChunk->isTimeout) {
                if ($responseChunk->isTimeout) {
                    yield new ObservableTaskStatusUpdate(ObservableTaskStatus::TimedOut, null);
                } else {
                    yield new ObservableTaskStatusUpdate(ObservableTaskStatus::Failed, null);
                }

                $this->onRequestFailure(
                    webSocketAuthResolution: $webSocketAuthResolution,
                    webSocketConnection: $webSocketConnection,
                    rpcRequest: $rpcRequest,
                );

                break;
            }

            yield new ObservableTaskStatusUpdate(ObservableTaskStatus::Running, null);

            $this->onResponseChunk(
                webSocketAuthResolution: $webSocketAuthResolution,
                webSocketConnection: $webSocketConnection,
                rpcRequest: $rpcRequest,
                responseChunk: $responseChunk->payload,
                isLastChunk: $responseChunk->isLastChunk,
            );
        }
    }
}
