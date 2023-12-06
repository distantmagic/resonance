<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\ApplicationConfiguration;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\DoctrineAttributeDriver;
use Distantmagic\Resonance\Environment;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Proxy\ProxyFactory;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * @template-extends SingletonProvider<Configuration>
 */
#[Singleton(provides: Configuration::class)]
final readonly class DoctrineORMConfigurationProvider extends SingletonProvider
{
    public function __construct(
        private ApplicationConfiguration $applicationConfiguration,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): Configuration
    {
        $isDevMode = Environment::Development === $this->applicationConfiguration->environment;

        $configuration = ORMSetup::createConfiguration(
            cache: new ArrayAdapter(storeSerialized: false),
            proxyDir: DM_ROOT.'/cache/doctrine',
            isDevMode: $isDevMode,
        );

        $configuration->setMetadataDriverImpl($this->buildMetadataDriver($phpProjectFiles));
        $configuration->setAutoGenerateProxyClasses(
            $isDevMode
                ? ProxyFactory::AUTOGENERATE_EVAL
                : ProxyFactory::AUTOGENERATE_NEVER
        );

        return $configuration;
    }

    /**
     * Ask Doctrine to scan JUST the Entities and nothing more.
     */
    private function buildMetadataDriver(PHPProjectFiles $phpProjectFiles): DoctrineAttributeDriver
    {
        $attributeDriver = new DoctrineAttributeDriver();

        foreach ($phpProjectFiles->findByAttribute(Entity::class) as $phpProjectfile) {
            $attributeDriver->addClassName($phpProjectfile->reflectionClass->getName());
        }

        return $attributeDriver;
    }
}
