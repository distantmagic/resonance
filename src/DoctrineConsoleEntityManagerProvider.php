<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\EntityManagerProvider;

#[Singleton]
readonly class DoctrineConsoleEntityManagerProvider implements EntityManagerProvider
{
    public function __construct(
        private Configuration $configuration,
        private DoctrineConnectionRepository $doctrineConnectionRepository,
    ) {}

    public function getDefaultManager(): EntityManagerInterface
    {
        return $this->getManager('default');
    }

    public function getManager(string $name): EntityManagerInterface
    {
        return new EntityManager(
            $this->doctrineConnectionRepository->buildConnection($name),
            $this->configuration,
        );
    }
}
