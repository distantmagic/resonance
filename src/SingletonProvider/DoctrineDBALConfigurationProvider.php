<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Doctrine\DBAL\Configuration;
use Doctrine\ORM\Configuration as ORMConfiguration;

/**
 * Reuses the ORM configuration as ORM config is just an extension of the base
 * DBAL config.
 *
 * @template-extends SingletonProvider<Configuration>
 */
#[Singleton(provides: Configuration::class)]
final readonly class DoctrineDBALConfigurationProvider extends SingletonProvider
{
    public function __construct(private ORMConfiguration $configuration) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): Configuration
    {
        return $this->configuration;
    }
}
