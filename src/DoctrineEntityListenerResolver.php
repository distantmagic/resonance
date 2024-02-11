<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Doctrine\ORM\Mapping\EntityListenerResolver;
use Ds\Map;
use InvalidArgumentException;
use LogicException;
use RuntimeException;

readonly class DoctrineEntityListenerResolver implements EntityListenerResolver
{
    /**
     * @var Map<class-string,object>
     */
    private Map $instances;

    public function __construct()
    {
        $this->instances = new Map();
    }

    public function clear($className = null): never
    {
        throw new LogicException('Listeners cannot be cleared on runtime');
    }

    /**
     * @param mixed $object explicitly mixed for typechecks
     */
    public function register($object): void
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException(sprintf(
                'Expected object, got "%s"',
                gettype($object)
            ));
        }

        if ($this->instances->hasKey($object::class)) {
            throw new RuntimeException(sprintf(
                'Listener is already registered: "%s"',
                $object::class,
            ));
        }

        $this->instances->put($object::class, $object);
    }

    /**
     * @param string $className
     */
    public function resolve($className): object
    {
        if (!class_exists($className)) {
            throw new InvalidArgumentException(sprintf(
                'Expected a class-string, got "%s"',
                $className
            ));
        }

        $listener = $this->instances->get($className, null);

        if (is_null($listener)) {
            throw new RuntimeException(sprintf(
                'Could not find listener: "%s"',
                $className,
            ));
        }

        return $listener;
    }
}
