<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\ApplicationConfiguration;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Environment;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\ORMSetup;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * @template-extends SingletonProvider<Configuration>
 */
#[Singleton(provides: Configuration::class)]
final readonly class DoctrineORMConfigurationProvider extends SingletonProvider
{
    public function __construct(private ApplicationConfiguration $applicationConfiguration) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): Configuration
    {
        return ORMSetup::createAttributeMetadataConfiguration(
            cache: new ArrayAdapter(storeSerialized: false),
            paths: [DM_APP_ROOT],
            isDevMode: Environment::Development === $this->applicationConfiguration->environment,
        );
    }
}
