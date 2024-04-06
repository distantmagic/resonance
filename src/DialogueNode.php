<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Set;

readonly class DialogueNode implements DialogueNodeInterface
{
    /**
     * @var Set<DialogueResponseInterface>
     */
    private Set $responses;

    /**
     * @var Set<DialogueNodeSideEffectInterface> $sideEffects
     */
    private Set $sideEffects;

    public function __construct(
        private DialogueMessageProducerInterface $message,
    ) {
        $this->responses = new Set();
        $this->sideEffects = new Set();
    }

    public function addPotentialResponse(DialogueResponseInterface $response): void
    {
        $this->responses->add($response);
    }

    public function addSideEffect(DialogueNodeSideEffectInterface $dialogueNodeSideEffect): void
    {
        $this->sideEffects->add($dialogueNodeSideEffect);
    }

    public function getMessageProducer(): DialogueMessageProducerInterface
    {
        return $this->message;
    }

    public function getSideEffects(): Set
    {
        return $this->sideEffects;
    }

    public function respondTo(DialogueInputInterface $dialogueInput): DialogueResponseResolutionInterface
    {
        foreach (new DialogueResponseSortedIterator($this->responses) as $response) {
            $resolution = $response->resolveResponse($dialogueInput);
            $resolutionStatus = $resolution->getStatus();

            if ($resolutionStatus->isFailed() || $resolutionStatus->canRespond()) {
                return $resolution;
            }
        }

        return new DialogueResponseResolution(
            followUp: null,
            status: DialogueResponseResolutionStatus::CannotRespond,
        );
    }
}
