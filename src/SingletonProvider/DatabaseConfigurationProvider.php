<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\ConfigurationFile;
use Distantmagic\Resonance\DatabaseConfiguration;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Nette\Schema\Expect;
use Nette\Schema\Processor;
use Nette\Schema\Schema;

/**
 * @template-extends SingletonProvider<DatabaseConfiguration>
 */
#[Singleton(provides: DatabaseConfiguration::class)]
final readonly class DatabaseConfigurationProvider extends SingletonProvider
{
    public function __construct(
        private ConfigurationFile $configurationFile,
        private Processor $processor,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): DatabaseConfiguration
    {
        /**
         * @var object{
         *     database: string,
         *     host: string,
         *     log_queries: bool,
         *     password: string,
         *     port: int,
         *     username: string,
         * }
         */
        $validatedData = $this->processor->process(
            $this->getSchema(),
            $this->configurationFile->config->get('database'),
        );

        return new DatabaseConfiguration(
            database: $validatedData->database,
            host: $validatedData->host,
            logQueries: $validatedData->log_queries,
            password: $validatedData->password,
            port: $validatedData->port,
            username: $validatedData->username,
        );
    }

    private function getSchema(): Schema
    {
        return Expect::structure([
            'database' => Expect::string()->min(1)->required(),
            'host' => Expect::string()->min(1)->required(),
            'log_queries' => Expect::bool()->required(),
            'password' => Expect::string()->required(),
            'port' => Expect::int()->min(1)->max(65535)->required(),
            'username' => Expect::string()->min(1)->required(),
        ]);
    }
}
