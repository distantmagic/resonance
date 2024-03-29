<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Ds\Map;
use LogicException;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Coroutine;
use Swoole\Coroutine\Context;
use WeakMap;

readonly class DoctrineEntityManagerRepository
{
    /**
     * @var WeakMap<ServerRequestInterface,Map<string,EntityManagerInterface>>
     */
    private WeakMap $entityManagers;

    public function __construct(
        private Configuration $configuration,
        private DoctrineConnectionRepository $doctrineConnectionRepository,
        private EventManager $eventManager,
    ) {
        /**
         * @var WeakMap<ServerRequestInterface,Map<string,EntityManagerInterface>>
         */
        $this->entityManagers = new WeakMap();
    }

    /**
     * @param non-empty-string $name
     */
    public function buildEntityManager(string $name = 'default'): EntityManagerInterface
    {
        return $this->buildEntityManagerFromConnection(
            connection: $this->doctrineConnectionRepository->buildConnection($name),
        );
    }

    /**
     * @param non-empty-string $name
     */
    public function createContextKey(string $name): string
    {
        return sprintf('%s.%s', self::class, $name);
    }

    /**
     * @param non-empty-string $name
     */
    public function getEntityManager(ServerRequestInterface $request, string $name = 'default'): EntityManagerInterface
    {
        if (!$this->entityManagers->offsetExists($request)) {
            $this->entityManagers->offsetSet($request, new Map());
        }

        $entityManagers = $this->entityManagers->offsetGet($request);

        if ($entityManagers->hasKey($name)) {
            return $entityManagers->get($name);
        }

        /**
         * @var null|Context $context
         */
        $context = Coroutine::getContext();
        $contextKey = $this->createContextKey($name);

        if ($context && isset($context[$contextKey]) && $context[$contextKey] instanceof EntityManagerWeakReference) {
            return $context[$contextKey]->getEntityManager();
        }

        $entityManager = $this->buildEntityManagerFromConnection(
            connection: $this->doctrineConnectionRepository->getConnection($request, $name),
        );

        if ($context) {
            $context[$contextKey] = new EntityManagerWeakReference($entityManager);
        }

        $entityManagers->put($name, $entityManager);

        return $entityManager;
    }

    /**
     * @template TCallbackReturn
     *
     * @param callable(EntityManagerInterface):TCallbackReturn $callback
     * @param non-empty-string                                 $name
     *
     * @return TCallbackReturn
     */
    public function withEntityManager(callable $callback, string $name = 'default', bool $flush = true): mixed
    {
        /**
         * @var null|Context $context
         */
        $context = Coroutine::getContext();
        $contextKey = $this->createContextKey($name);

        if ($context && isset($context[$contextKey])) {
            $entityManagerWeakReference = $context[$contextKey];

            if (!($entityManagerWeakReference instanceof EntityManagerWeakReference)) {
                throw new LogicException('Expected weak reference to entity manager');
            }
        } else {
            $entityManagerWeakReference = $this->getWeakReference($name);

            if ($context) {
                $context[$contextKey] = $entityManagerWeakReference;
            }
        }

        $entityManager = $entityManagerWeakReference->getEntityManager();

        try {
            return $callback($entityManager);
        } finally {
            if ($flush) {
                $entityManager->flush();
            }
        }
    }

    /**
     * @template TEntityClass of object
     * @template TEntityRepository of EntityRepository<TEntityClass>
     * @template TCallbackReturn
     *
     * @param class-string<TEntityClass>                                         $className
     * @param callable(EntityManagerInterface,TEntityRepository):TCallbackReturn $callback
     * @param non-empty-string                                                   $name
     *
     * @return TCallbackReturn
     */
    public function withRepository(string $className, callable $callback, string $name = 'default', bool $flush = true): mixed
    {
        return $this->withEntityManager(static function (EntityManagerInterface $entityManager) use ($className, $callback) {
            /**
             * @var TEntityRepository
             */
            $repository = $entityManager->getRepository($className);

            return $callback($entityManager, $repository);
        }, $name, $flush);
    }

    private function buildEntityManagerFromConnection(Connection $connection): EntityManagerInterface
    {
        return new EntityManager(
            conn: $connection,
            config: $this->configuration,
            eventManager: $this->eventManager,
        );
    }

    /**
     * @param non-empty-string $name
     */
    private function getWeakReference(string $name = 'default'): EntityManagerWeakReference
    {
        return new EntityManagerWeakReference($this->buildEntityManager($name));
    }
}
