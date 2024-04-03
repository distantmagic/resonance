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

    public function __construct(private DialogueMessageProducerInterface $message)
    {
        $this->responses = new Set();
    }

    public function addPotentialResponse(DialogueResponseInterface $response): void
    {
        $this->responses->add($response);
    }

    public function getMessageProducer(): DialogueMessageProducerInterface
    {
        return $this->message;
    }

    public function respondTo(DialogueInputInterface $dialogueInput): ?DialogueNodeInterface
    {
        foreach (new DialogueResponseSortedIterator($this->responses) as $response) {
            if ($response->getCondition()->isMetBy($dialogueInput)) {
                return $response->getFollowUp();
            }
        }

        return null;
    }
}
