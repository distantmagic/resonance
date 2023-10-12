<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\DatabaseConfiguration;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

/**
 * @template-extends ConfigurationProvider<DatabaseConfiguration, object{
 *     database: string,
 *     host: string,
 *     log_queries: bool,
 *     password: string,
 *     port: int,
 *     username: string,
 * }>
 */
#[Singleton(provides: DatabaseConfiguration::class)]
final readonly class DatabaseConfigurationProvider extends ConfigurationProvider
{
    protected function getConfigurationKey(): string
    {
        return 'database';
    }

    protected function getSchema(): Schema
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

    protected function provideConfiguration(object $validatedData): DatabaseConfiguration
    {
        return new DatabaseConfiguration(
            database: $validatedData->database,
            host: $validatedData->host,
            logQueries: $validatedData->log_queries,
            password: $validatedData->password,
            port: $validatedData->port,
            username: $validatedData->username,
        );
    }
}
