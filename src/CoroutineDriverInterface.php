<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

interface CoroutineDriverInterface
{
    /**
     * @param array<callable()> $callbacks
     *
     * @throws CoroutineDriverException
     *
     * @return array mapped values
     */
    public function batch(array $callbacks): array;

    /**
     * @psalm-suppress PossiblyUnusedReturnValue used in apps
     *
     * @param callable() $callback
     *
     * @throws CoroutineDriverException
     */
    public function go(callable $callback): CoroutineReferenceInterface;

    /**
     * @throws CoroutineDriverException
     */
    public function init(): void;

    /**
     * Start the event loop
     *
     * @template TReturn
     *
     * @param callable():TReturn $callback
     *
     * @throws CoroutineDriverException
     *
     * @return TReturn
     */
    public function run(callable $callback): mixed;

    /**
     * Wait for all coroutines to finish.
     */
    public function wait(): void;
}
