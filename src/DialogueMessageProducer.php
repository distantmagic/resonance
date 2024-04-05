<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

abstract readonly class DialogueMessageProducer implements DialogueMessageProducerInterface
{
    public function __toString(): string
    {
        $ret = '';

        foreach ($this as $chunk) {
            $ret .= (string) $chunk;
        }

        return $ret;
    }
}
