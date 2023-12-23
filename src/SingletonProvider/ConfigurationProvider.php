<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\ConfigurationFile;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Nette\Schema\Processor;
use Nette\Schema\Schema;

/**
 * @template TObject of object
 * @template TSchema
 *
 * @template-extends SingletonProvider<TObject>
 */
abstract readonly class ConfigurationProvider extends SingletonProvider
{
    abstract protected function getConfigurationKey(): string;

    abstract protected function getSchema(): Schema;

    /**
     * @param TSchema $validatedData
     *
     * @return TObject
     */
    abstract protected function provideConfiguration($validatedData): object;

    public function __construct(
        private ConfigurationFile $configurationFile,
        private Processor $processor,
    ) {}

    /**
     * @return TObject
     */
    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): object
    {
        /**
         * @var TSchema $validatedData
         */
        $validatedData = $this->processor->process(
            $this->getSchema(),
            $this->configurationFile->config->get($this->getConfigurationKey()),
        );

        return $this->provideConfiguration($validatedData);
    }

    public function shouldRegister(): bool
    {
        return $this->configurationFile->config->has($this->getConfigurationKey());
    }
}
