<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\DialogueMessageProducer;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\DialogueMessageChunk;
use Distantmagic\Resonance\DialogueMessageProducer;
use Generator;

#[Singleton]
readonly class EmptyMessageProducer extends DialogueMessageProducer
{
    public function getIterator(): Generator
    {
        yield new DialogueMessageChunk(
            content: '',
            isFailed: false,
            isLastToken: true,
        );
    }
}
