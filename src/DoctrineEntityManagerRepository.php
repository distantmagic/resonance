<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Http\Message\ServerRequestInterface;

readonly class DoctrineEntityManagerRepository
{
    public function __construct(
        private Configuration $configuration,
        private DoctrineConnectionRepository $doctrineConnectionRepository,
        private DoctrineEntityManagerWeakStore $doctrineEntityManagerWeakStore,
        private EventManager $eventManager,
    ) {}

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
    public function getEntityManager(ServerRequestInterface|WebSocketConnection $request, string $name = 'default'): EntityManagerInterface
    {
        $entityManager = $this->doctrineEntityManagerWeakStore->fromRequest($request, $name);

        if ($entityManager) {
            return $entityManager;
        }

        $entityManagerWeakReference = $this->doctrineEntityManagerWeakStore->fromContext($name);

        if ($entityManagerWeakReference) {
            return $entityManagerWeakReference->getEntityManager();
        }

        $entityManager = $this->buildEntityManagerFromConnection(
            connection: $this->doctrineConnectionRepository->getConnection($request, $name),
        );

        $this->doctrineEntityManagerWeakStore->setInContext($name, new EntityManagerWeakReference($entityManager));
        $this->doctrineEntityManagerWeakStore->setByRequest($request, $name, $entityManager);

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
        $entityManagerWeakReference = $this->doctrineEntityManagerWeakStore->fromContext($name);

        if (!$entityManagerWeakReference) {
            $entityManagerWeakReference = $this->buildWeakReference($name);

            $this->doctrineEntityManagerWeakStore->setInContext($name, $entityManagerWeakReference);
        }

        $entityManager = $entityManagerWeakReference->getEntityManager();

        $ret = $callback($entityManager);

        if ($flush) {
            $entityManager->flush();
        }

        return $ret;
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
    private function buildWeakReference(string $name = 'default'): EntityManagerWeakReference
    {
        return new EntityManagerWeakReference($this->buildEntityManager($name));
    }
}
