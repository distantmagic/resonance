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

    public function __construct(
        private DialogueMessageProducerInterface $message,
        private DialogueResponseDiscriminatorInterface $responseDiscriminator,
    ) {
        $this->responses = new Set();
    }

    public function addResponse(DialogueResponseInterface $response): void
    {
        $this->responses->add($response);
    }

    public function getMessageProducer(): DialogueMessageProducerInterface
    {
        return $this->message;
    }

    public function respondTo(DialogueInputInterface $prompt): ?DialogueNodeInterface
    {
        return $this->responseDiscriminator->discriminate($this->responses, $prompt);
    }
}
