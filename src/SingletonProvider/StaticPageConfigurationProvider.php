<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\ConfigurationFile;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Distantmagic\Resonance\StaticPageConfiguration;
use Nette\Schema\Expect;
use Nette\Schema\Processor;
use Nette\Schema\Schema;

/**
 * @template-extends SingletonProvider<StaticPageConfiguration>
 */
#[Singleton(provides: StaticPageConfiguration::class)]
final readonly class StaticPageConfigurationProvider extends SingletonProvider
{
    public function __construct(
        private ConfigurationFile $configurationFile,
        private Processor $processor,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): StaticPageConfiguration
    {
        /**
         * @var object{
         *     base_url: string,
         * }
         */
        $validatedData = $this->processor->process(
            $this->getSchema(),
            $this->configurationFile->config->get('static'),
        );

        return new StaticPageConfiguration(
            baseUrl: $validatedData->base_url,
        );
    }

    private function getSchema(): Schema
    {
        return Expect::structure([
            'base_url' => Expect::string()->min(1)->required(),
        ]);
    }
}
