<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\ApplicationConfiguration;
use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\DoctrineConnectionRepository;
use Distantmagic\Resonance\DoctrineEntityManagerRepository;
use Distantmagic\Resonance\Environment;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Doctrine\ORM\Configuration;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @template-extends SingletonProvider<DoctrineEntityManagerRepository>
 */
#[GrantsFeature(Feature::Doctrine)]
#[Singleton(provides: DoctrineEntityManagerRepository::class)]
final readonly class DoctrineEntityManagerRepositoryProvider extends SingletonProvider
{
    public function __construct(
        private ApplicationConfiguration $applicationConfiguration,
        private DoctrineConnectionRepository $doctrineConnectionRepository,
        private Configuration $configuration,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): DoctrineEntityManagerRepository
    {
        $doctrineEntityManagerRepository = new DoctrineEntityManagerRepository(
            $this->doctrineConnectionRepository,
            $this->configuration,
        );

        if (Environment::Development !== $this->applicationConfiguration->environment) {
            $this->generateProxies($doctrineEntityManagerRepository);
        }

        return $doctrineEntityManagerRepository;
    }

    private function generateProxies(DoctrineEntityManagerRepository $doctrineEntityManagerRepository): void
    {
        $cacheDirectory = DM_ROOT.'/cache/doctrine';

        $filesystem = new Filesystem();
        $filesystem->remove($cacheDirectory);

        $entityManager = $doctrineEntityManagerRepository->buildEntityManager();

        $entityManager->getProxyFactory()->generateProxyClasses(
            $entityManager->getMetadataFactory()->getAllMetadata(),
            $cacheDirectory,
        );

        /**
         * Doctrine starts a database connection to prebuild the proxy classes.
         */
        $entityManager->getConnection()->close();
    }
}
