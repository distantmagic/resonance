<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Ds\Sequence;
use Psr\Container\ContainerInterface;

/**
 * PSR Container wants 'string' in parameters. Singleton container uses
 * 'class-string', which is more specific. For the sake of typechecking it
 * might be safer to stay more specific in the derived interface even if it
 * potentially results in more potential type errors thrown around.
 *
 * @psalm-suppress MoreSpecificImplementedParamType
 */
interface SingletonContainerInterface extends ContainerInterface
{
    /**
     * @template TSingleton
     *
     * @param class-string<TSingleton> $id
     *
     * @return TSingleton
     */
    public function get(string $id): object;

    /**
     * @param class-string $id
     */
    public function has(string $id): bool;

    /**
     * @template TSingleton
     *
     * @param class-string<TSingleton> $id
     * @param TSingleton               $object
     */
    public function set(string $id, object $object): void;

    /**
     * @return Sequence<object>
     */
    public function values(): Sequence;
}
