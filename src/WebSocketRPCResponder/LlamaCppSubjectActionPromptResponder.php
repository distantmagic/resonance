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
use Distantmagic\Resonance\PromptSubjectResponderAggregate;
use Distantmagic\Resonance\RPCNotification;
use Distantmagic\Resonance\RPCRequest;
use Distantmagic\Resonance\WebSocketAuthResolution;
use Distantmagic\Resonance\WebSocketConnection;
use Distantmagic\Resonance\WebSocketRPCResponder;
use Psr\Log\LoggerInterface;
use RuntimeException;
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

    public function onNotification(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        RPCNotification $rpcNotification,
    ): never {
        throw new RuntimeException('Unexpected notification');
    }

    public function onRequest(
        WebSocketAuthResolution $webSocketAuthResolution,
        WebSocketConnection $webSocketConnection,
        RPCRequest $rpcRequest,
    ): void {
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
                timeout: 1.0,
            )
        ;

        /**
         * @var mixed $responseChunk explicitly mixed for typechecks
         */
        foreach ($response as $responseChunk) {
            $this->onResponseChunk(
                webSocketAuthResolution: $webSocketAuthResolution,
                webSocketConnection: $webSocketConnection,
                rpcRequest: $rpcRequest,
                responseChunk: $responseChunk,
                isLastChunk: false,
            );
        }

        $this->onResponseChunk(
            webSocketAuthResolution: $webSocketAuthResolution,
            webSocketConnection: $webSocketConnection,
            rpcRequest: $rpcRequest,
            responseChunk: '',
            isLastChunk: true,
        );
    }
}
