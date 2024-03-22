<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\WebSocketRPCResponder;

use Distantmagic\Resonance\BackusNaurFormGrammar\SubjectActionGrammar;
use Distantmagic\Resonance\LlamaCppClient;
use Distantmagic\Resonance\LlamaCppCompletionIterator;
use Distantmagic\Resonance\LlamaCppCompletionRequest;
use Distantmagic\Resonance\LlmPrompt\SubjectActionPrompt;
use Distantmagic\Resonance\LlmPromptTemplate;
use Distantmagic\Resonance\LlmPromptTemplate\ChainPrompt;
use Distantmagic\Resonance\ObservableTask;
use Distantmagic\Resonance\ObservableTaskStatus;
use Distantmagic\Resonance\ObservableTaskStatusUpdate;
use Distantmagic\Resonance\ObservableTaskTable;
use Distantmagic\Resonance\ObservableTaskTimeoutIterator;
use Distantmagic\Resonance\PromptSubjectResponderAggregate;
use Distantmagic\Resonance\RPCRequest;
use Distantmagic\Resonance\WebSocketAuthResolution;
use Distantmagic\Resonance\WebSocketConnection;
use Distantmagic\Resonance\WebSocketRPCResponder;
use Generator;
use Psr\Log\LoggerInterface;
use WeakMap;

/**
 * @template TPayload
 *
 * @template-extends WebSocketRPCResponder<TPayload>
 */
abstract readonly class LlamaCppSubjectActionPromptResponder extends WebSocketRPCResponder
{
    /**
     * @var WeakMap<WebSocketConnection,LlamaCppCompletionIterator>
     */
    private WeakMap $runningCompletions;

    /**
     * @param TPayload $payload
     */
    abstract protected function getPromptFromPayload(mixed $payload): string;

    abstract protected function onResponseChunk(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        RPCRequest $rpcRequest,
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
        RPCRequest $rpcRequest,
    ): void {
        $this->observableTaskTable->observe(new ObservableTask(
            new ObservableTaskTimeoutIterator(
                iterableTask: function () use (
                    $webSocketAuthResolution,
                    $webSocketConnection,
                    $rpcRequest,
                ): Generator {
                    yield new ObservableTaskStatusUpdate(ObservableTaskStatus::Running, null);

                    try {
                        yield from $this->onObservableRequest(
                            $webSocketAuthResolution,
                            $webSocketConnection,
                            $rpcRequest,
                        );
                    } finally {
                        yield new ObservableTaskStatusUpdate(ObservableTaskStatus::Finished, null);
                    }
                },
                inactivityTimeout: 1.0,
            )
        ));
    }

    /**
     * @param RPCRequest<TPayload> $rpcRequest
     *
     * @return Generator<ObservableTaskStatusUpdate>
     */
    private function onObservableRequest(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        RPCRequest $rpcRequest,
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
            if ($responseChunk->isFailed) {
                yield new ObservableTaskStatusUpdate(ObservableTaskStatus::Failed, null);

                break;
            }

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
