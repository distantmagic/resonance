<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\ConfigurationFile;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\RedisConfiguration;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Nette\Schema\Expect;
use Nette\Schema\Processor;
use Nette\Schema\Schema;

/**
 * @template-extends SingletonProvider<RedisConfiguration>
 */
#[Singleton(provides: RedisConfiguration::class)]
final readonly class RedisConfigurationProvider extends SingletonProvider
{
    public function __construct(
        private ConfigurationFile $configurationFile,
        private Processor $processor,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): RedisConfiguration
    {
        /**
         * @var object{
         *     host: string,
         *     password: string,
         *     port: int,
         *     prefix: string,
         * }
         */
        $validatedData = $this->processor->process(
            $this->getSchema(),
            $this->configurationFile->config->get('redis'),
        );

        return new RedisConfiguration(
            host: $validatedData->host,
            password: $validatedData->password,
            port: $validatedData->port,
            prefix: $validatedData->prefix,
        );
    }

    private function getSchema(): Schema
    {
        return Expect::structure([
            'host' => Expect::string()->min(1)->required(),
            'password' => Expect::string()->required(),
            'port' => Expect::int()->min(1)->max(65535)->required(),
            'prefix' => Expect::string()->min(1)->required(),
        ]);
    }
}
