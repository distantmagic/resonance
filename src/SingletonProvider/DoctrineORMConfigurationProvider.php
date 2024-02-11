<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\ApplicationConfiguration;
use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\DoctrineAttributeDriver;
use Distantmagic\Resonance\DoctrineEntityListenerResolver;
use Distantmagic\Resonance\Environment;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Proxy\ProxyFactory;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * @template-extends SingletonProvider<Configuration>
 */
#[GrantsFeature(Feature::Doctrine)]
#[Singleton(provides: Configuration::class)]
final readonly class DoctrineORMConfigurationProvider extends SingletonProvider
{
    public function __construct(
        private ApplicationConfiguration $applicationConfiguration,
        private DoctrineAttributeDriver $doctrineAttributeDriver,
        private DoctrineEntityListenerResolver $doctrineEntityListenerResolver,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): Configuration
    {
        $isDevMode = Environment::Development === $this->applicationConfiguration->environment;

        $cache = new ArrayAdapter(storeSerialized: false);

        $configuration = ORMSetup::createConfiguration(
            cache: $cache,
            proxyDir: DM_ROOT.'/cache/doctrine',
            isDevMode: $isDevMode,
        );

        $configuration->setEntityListenerResolver($this->doctrineEntityListenerResolver);
        $configuration->setMetadataDriverImpl($this->doctrineAttributeDriver);
        $configuration->setAutoGenerateProxyClasses(
            $isDevMode
                ? ProxyFactory::AUTOGENERATE_EVAL
                : ProxyFactory::AUTOGENERATE_NEVER
        );

        return $configuration;
    }
}
