<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TData
 */
readonly class SwooleChannelIteratorChunk
{
    /**
     * @param TData $data
     */
    public function __construct(public mixed $data) {}
}
