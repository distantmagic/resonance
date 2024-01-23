<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TPipeMessage of ServerPipeMessageInterface
 */
interface ServerPipeMessageHandlerInterface
{
    /**
     * I wish PHP had generics.
     *
     * @param TPipeMessage $serverPipeMessage
     */
    public function handleServerPipeMessage(ServerPipeMessageInterface $serverPipeMessage): void;
}
