<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Ds\Map;
use LogicException;
use Swoole\Coroutine;
use Swoole\Coroutine\Context;
use Swoole\Http\Request;
use WeakMap;

readonly class DoctrineEntityManagerRepository
{
    /**
     * @var WeakMap<Request,Map<string,EntityManagerInterface>>
     */
    private WeakMap $entityManagers;

    public function __construct(
        private DoctrineConnectionRepository $doctrineConnectionRepository,
        private Configuration $configuration,
    ) {
        /**
         * @var WeakMap<Request,Map<string,EntityManagerInterface>>
         */
        $this->entityManagers = new WeakMap();
    }

    public function buildEntityManager(string $name = 'default'): EntityManagerInterface
    {
        return new EntityManager(
            $this->doctrineConnectionRepository->buildConnection($name),
            $this->configuration,
        );
    }

    public function getEntityManager(Request $request, string $name = 'default'): EntityManagerInterface
    {
        if (!$this->entityManagers->offsetExists($request)) {
            $this->entityManagers->offsetSet($request, new Map());
        }

        $entityManagers = $this->entityManagers->offsetGet($request);

        if ($entityManagers->hasKey($name)) {
            return $entityManagers->get($name);
        }

        $conn = $this->doctrineConnectionRepository->getConnection($request, $name);
        $entityManager = new EntityManager($conn, $this->configuration);

        $entityManagers->put($name, $entityManager);

        return $entityManager;
    }

    /**
     * @template TCallbackReturn
     *
     * @param callable(EntityManagerInterface):TCallbackReturn $callback
     *
     * @return TCallbackReturn
     */
    public function withEntityManager(callable $callback, string $name = 'default', bool $flush = true): mixed
    {
        /**
         * @var null|Context $context
         */
        $context = Coroutine::getContext();

        $contextKey = sprintf('%s.entityManager.%s', __METHOD__, $name);

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

    private function getWeakReference(string $name = 'default'): EntityManagerWeakReference
    {
        return new EntityManagerWeakReference($this->buildEntityManager($name));
    }
}
