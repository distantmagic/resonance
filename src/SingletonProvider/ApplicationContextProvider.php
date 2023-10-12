<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\ApplicationContext;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\ConfigurationFile;
use Distantmagic\Resonance\Environment;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;

/**
 * @template-extends SingletonProvider<ApplicationContext>
 */
#[Singleton(provides: ApplicationContext::class)]
final readonly class ApplicationContextProvider extends SingletonProvider
{
    public function __construct(private ConfigurationFile $configurationFile) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): ApplicationContext
    {
        $environment = Environment::from((string) $this->configurationFile->config->get('app.env'));

        return new ApplicationContext($environment);
    }
}
