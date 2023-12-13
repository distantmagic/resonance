<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\SingletonContainerException\NotFoundException;
use Ds\Map;
use Ds\Sequence;

/**
 * ParamType suppression is a compromise to implement Psr container
 *
 * @psalm-suppress MoreSpecificImplementedParamType
 *
 * @see SingletonContainerInterface psalm-suppress explanation
 */
final readonly class SingletonContainer implements SingletonContainerInterface
{
    /**
     * @var Map<class-string, object>
     */
    private Map $singletons;

    private static function assertClassExists(string $class): void
    {
        if (!class_exists($class) && !interface_exists($class)) {
            throw new SingletonContainerException('Class does not exist: '.$class);
        }
    }

    public function __construct()
    {
        $this->singletons = new Map();
    }

    /**
     * @template TSingleton
     *
     * @param class-string<TSingleton> $id
     *
     * @return TSingleton
     */
    public function get(string $id): object
    {
        self::assertClassExists($id);

        if (!$this->has($id)) {
            throw new NotFoundException('Class is not set: '.$id);
        }

        /**
         * @var TSingleton
         */
        return $this->singletons->get($id);
    }

    public function has(string $id): bool
    {
        self::assertClassExists($id);

        return $this->singletons->hasKey($id);
    }

    public function set(string $id, object $object): void
    {
        self::assertClassExists($id);

        if ($this->has($id)) {
            throw new SingletonContainerException('Class is already set: '.$id);
        }

        /**
         * Even though the dockblock suggests that this check is redundant,
         * it's important to note that the object might not necessarily be an
         * instance of the class, especially if type information is disregarded.
         *
         * @psalm-suppress DocblockTypeContradiction
         */
        if (!($object instanceof $id)) {
            /**
             * @var class-string $className
             */
            $className = $object::class;

            throw new SingletonContainerException(sprintf(
                'Object %s is not instance of %s',
                $className,
                $id,
            ));
        }

        $this->singletons->put($id, $object);
    }

    public function values(): Sequence
    {
        return $this->singletons->values();
    }
}
