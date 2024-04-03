<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface DialogueNodeInterface
{
    public function addResponse(DialogueResponseInterface $response): void;

    public function getMessageProducer(): DialogueMessageProducerInterface;

    public function respondTo(DialogueInputInterface $prompt): ?self;
}
