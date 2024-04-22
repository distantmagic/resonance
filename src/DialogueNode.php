<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\DialogueMessageProducer\ConstMessageProducer;
use Ds\Set;
use Generator;

use function Distantmagic\Resonance\helpers\generatorGetReturn;

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

    public static function withMessage(string $content): self
    {
        return new self(
            message: new ConstMessageProducer($content),
        );
    }

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

    public function copyResponsesFrom(DialogueNodeInterface $other): void
    {
        foreach ($other->getPotentialResponses() as $response) {
            $this->addPotentialResponse($response);
        }
    }

    public function getMessageProducer(): DialogueMessageProducerInterface
    {
        return $this->message;
    }

    /**
     * @return Set<DialogueResponseInterface>
     */
    public function getPotentialResponses(): Set
    {
        return $this->responses->copy();
    }

    public function getSideEffects(): Set
    {
        return $this->sideEffects;
    }

    public function respondTo(DialogueInputInterface $dialogueInput): DialogueResponseResolutionInterface
    {
        $respondToGenerator = $this->respondToWithProgress($dialogueInput);

        return generatorGetReturn($respondToGenerator);
    }

    public function respondToWithProgress(DialogueInputInterface $dialogueInput): Generator
    {
        foreach (new DialogueResponseSortedIterator($this->responses) as $response) {
            $resolutionGenerator = $response->resolveResponseWithProgress($dialogueInput);

            yield from $resolutionGenerator;

            $resolution = $resolutionGenerator->getReturn();
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
