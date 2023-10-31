<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Ds\Map;
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
}
