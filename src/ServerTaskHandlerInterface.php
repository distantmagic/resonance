<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

/**
 * @template TTask of object
 */
interface ServerTaskHandlerInterface
{
    /**
     * I wish PHP had generics.
     *
     * @param TTask $serverTask
     */
    public function handleServerTask(object $serverTask): void;
}
