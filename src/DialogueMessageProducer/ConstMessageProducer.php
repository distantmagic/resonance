<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\DialogueMessageProducer;

use Distantmagic\Resonance\DialogueMessageChunk;
use Distantmagic\Resonance\DialogueMessageProducer;
use Generator;

readonly class ConstMessageProducer extends DialogueMessageProducer
{
    public function __construct(
        public string $content,
    ) {}

    /**
     * @return Generator<DialogueMessageChunk>
     */
    public function getIterator(): Generator
    {
        yield new DialogueMessageChunk(
            content: $this->content,
            isFailed: false,
            isLastToken: true,
        );
    }
}
