<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\DatabaseConfiguration;
use Distantmagic\Resonance\DatabaseConnectionPoolConfiguration;
use Distantmagic\Resonance\DatabaseConnectionPoolDriverName;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

/**
 * @template-extends ConfigurationProvider<
 *     DatabaseConfiguration,
 *     array<string, object{
 *         database: string,
 *         driver: string,
 *         host: string,
 *         log_queries: bool,
 *         password: string,
 *         pool_prefill: bool,
 *         pool_size: int,
 *         port: int,
 *         username: string,
 *     }>
 * >
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
        $keySchema = Expect::string()->min(1)->required();

        $valueSchema = Expect::structure([
            'database' => Expect::string()->min(1)->required(),
            'driver' => Expect::anyOf(...DatabaseConnectionPoolDriverName::values())->required(),
            'host' => Expect::string()->min(1)->required(),
            'log_queries' => Expect::bool()->required(),
            'password' => Expect::string()->required(),
            'pool_prefill' => Expect::bool()->required(),
            'pool_size' => Expect::int()->min(1)->required(),
            'port' => Expect::int()->min(1)->max(65535)->required(),
            'username' => Expect::string()->min(1)->required(),
        ]);

        return Expect::arrayOf($valueSchema, $keySchema);
    }

    protected function provideConfiguration($validatedData): DatabaseConfiguration
    {
        $databaseconfiguration = new DatabaseConfiguration();

        foreach ($validatedData as $name => $connectionPoolConfiguration) {
            $databaseconfiguration->connectionPoolConfiguration->put(
                $name,
                new DatabaseConnectionPoolConfiguration(
                    database: $connectionPoolConfiguration->database,
                    driver: DatabaseConnectionPoolDriverName::from($connectionPoolConfiguration->driver),
                    host: $connectionPoolConfiguration->host,
                    logQueries: $connectionPoolConfiguration->log_queries,
                    password: $connectionPoolConfiguration->password,
                    poolPrefill: $connectionPoolConfiguration->pool_prefill,
                    poolSize: $connectionPoolConfiguration->pool_size,
                    port: $connectionPoolConfiguration->port,
                    username: $connectionPoolConfiguration->username,
                ),
            );
        }

        return $databaseconfiguration;
    }
}
