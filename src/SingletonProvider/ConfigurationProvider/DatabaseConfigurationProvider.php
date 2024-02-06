<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\Constraint\BooleanConstraint;
use Distantmagic\Resonance\Constraint\EnumConstraint;
use Distantmagic\Resonance\Constraint\IntegerConstraint;
use Distantmagic\Resonance\Constraint\MapConstraint;
use Distantmagic\Resonance\Constraint\ObjectConstraint;
use Distantmagic\Resonance\Constraint\StringConstraint;
use Distantmagic\Resonance\DatabaseConfiguration;
use Distantmagic\Resonance\DatabaseConnectionPoolConfiguration;
use Distantmagic\Resonance\DatabaseConnectionPoolDriverName;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

/**
 * @template-extends ConfigurationProvider<
 *     DatabaseConfiguration,
 *     array<non-empty-string, array{
 *         database: string,
 *         driver: string,
 *         host: non-empty-string,
 *         log_queries: bool,
 *         password: string,
 *         pool_prefill: bool,
 *         pool_size: int,
 *         port: int,
 *         unix_socket: null|non-empty-string,
 *         username: non-empty-string,
 *     }>
 * >
 */
#[Singleton(provides: DatabaseConfiguration::class)]
final readonly class DatabaseConfigurationProvider extends ConfigurationProvider
{
    public function getConstraint(): Constraint
    {
        $valueConstraint = new ObjectConstraint([
            'database' => new StringConstraint(),
            'driver' => new EnumConstraint(DatabaseConnectionPoolDriverName::values()),
            'host' => (new StringConstraint())->default(null),
            'log_queries' => new BooleanConstraint(),
            'password' => (new StringConstraint())->nullable(),
            'pool_prefill' => (new BooleanConstraint())->default(true),
            'pool_size' => new IntegerConstraint(),
            'port' => (new IntegerConstraint())->nullable()->default(3306),
            'unix_socket' => (new StringConstraint())->nullable(),
            'username' => new StringConstraint(),
        ]);

        return new MapConstraint(valueConstraint: $valueConstraint);
    }

    protected function getConfigurationKey(): string
    {
        return 'database';
    }

    protected function provideConfiguration($validatedData): DatabaseConfiguration
    {
        $databaseconfiguration = new DatabaseConfiguration();

        foreach ($validatedData as $name => $connectionPoolConfiguration) {
            $databaseconfiguration->connectionPoolConfiguration->put(
                $name,
                new DatabaseConnectionPoolConfiguration(
                    database: $connectionPoolConfiguration['database'],
                    driver: DatabaseConnectionPoolDriverName::from($connectionPoolConfiguration['driver']),
                    host: $connectionPoolConfiguration['host'],
                    logQueries: $connectionPoolConfiguration['log_queries'],
                    password: $connectionPoolConfiguration['password'],
                    poolPrefill: $connectionPoolConfiguration['pool_prefill'],
                    poolSize: $connectionPoolConfiguration['pool_size'],
                    port: $connectionPoolConfiguration['port'],
                    unixSocket: $connectionPoolConfiguration['unix_socket'],
                    username: $connectionPoolConfiguration['username'],
                ),
            );
        }

        return $databaseconfiguration;
    }
}
