<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use WeakReference;

/**
 * WeakReference guarantees that EntityManager's connection will be closed when
 * the object is disposed of.
 */
readonly class EntityManagerWeakReference
{
    /**
     * @var WeakReference<EntityManagerInterface> $reference
     */
    public WeakReference $reference;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->reference = WeakReference::create($entityManager);
    }

    public function __destruct()
    {
        $this->getEntityManager()->getConnection()->close();
    }

    public function getEntityManager(): EntityManagerInterface
    {
        $entityManager = $this->reference->get();

        if (!$entityManager) {
            throw new RuntimeException('Entity manager refernce is garbage collected');
        }

        return $entityManager;
    }

    public function hasEntityManager(): bool
    {
        return !is_null($this->reference->get());
    }
}
