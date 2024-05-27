<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\RequiresBackendDriver;
use Distantmagic\Resonance\Attribute\Singleton;
use RuntimeException;
use Swoole\Server;

#[RequiresBackendDriver(BackendDriver::Swoole)]
#[Singleton]
readonly class ServerPipeMessageDispatcher
{
    public function __construct(
        private ServerPipeMessageHandlerCollection $serverPipeMessageHandlerCollection,
    ) {}

    /**
     * Although it's unused, that param makes it compatbile with Swoole server
     * event callbacks.
     *
     * @psalm-suppress PossiblyUnusedParam
     */
    public function onPipeMessage(Server $server, int $srcWorkerId, mixed $data): void
    {
        if (!is_object($data)) {
            throw new RuntimeException('Only objects can be used as server pipe messages');
        }

        if (!($data instanceof ServerPipeMessageInterface)) {
            throw new RuntimeException(sprintf(
                'Expected "%s" as an instance of server pipe message, got: "%s"',
                ServerPipeMessageInterface::class,
                $data::class,
            ));
        }

        $handlers = $this
            ->serverPipeMessageHandlerCollection
            ->serverPipeMessageHandler
            ->get($data::class, null)
        ;

        if (empty($handlers)) {
            return;
        }

        foreach ($handlers as $handler) {
            $handler->handleServerPipeMessage($data);
        }
    }
}
