<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use IteratorAggregate;
use Swoole\Coroutine\Channel;

/**
 * @template-implements IteratorAggregate<mixed>
 */
readonly class PromptSubjectResponse implements IteratorAggregate
{
    private Channel $channel;

    public function __construct()
    {
        $this->channel = new Channel(1);
    }

    public function __destruct()
    {
        $this->channel->close();
    }

    /**
     * @return SwooleChannelIterator<mixed>
     */
    public function getIterator(): SwooleChannelIterator
    {
        /**
         * @var SwooleChannelIterator<mixed>
         */
        return new SwooleChannelIterator($this->channel);
    }

    /**
     * @return mixed because almost every PHP type can be send over websocket
     */
    public function write(mixed $payload): bool
    {
        return $this->channel->push($payload);
    }
}
