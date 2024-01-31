<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Ds\Set;
use Swoole\Server;
use Symfony\Component\Messenger\Envelope;

#[Singleton]
readonly class SwooleTaskServerMessageBroker
{
    /**
     * @var Set<Server>
     */
    public Set $runningServers;

    public function __construct()
    {
        $this->runningServers = new Set();
    }

    public function dispatch(Envelope $message): void
    {
        foreach ($this->runningServers as $server) {
            /**
             * Wrong types, message can be mixed.
             *
             * @psalm-suppress InvalidArgument
             * @psalm-suppress InvalidCast
             */
            $server->task($message);
        }
    }
}
