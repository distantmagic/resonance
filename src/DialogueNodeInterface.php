<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Set;

interface DialogueNodeInterface
{
    public function addPotentialResponse(DialogueResponseInterface $response): void;

    public function addSideEffect(DialogueNodeSideEffectInterface $dialogueNodeSideEffect): void;

    public function copyResponsesFrom(self $other): void;

    public function getMessageProducer(): DialogueMessageProducerInterface;

    /**
     * @return Set<DialogueResponseInterface>
     */
    public function getPotentialResponses(): Set;

    /**
     * @return Set<DialogueNodeSideEffectInterface>
     */
    public function getSideEffects(): Set;

    public function respondTo(DialogueInputInterface $dialogueInput): DialogueResponseResolutionInterface;
}
