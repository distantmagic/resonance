<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\DatabaseConfiguration;
use Distantmagic\Resonance\DatabaseConnectionPoolConfiguration;
use Distantmagic\Resonance\DatabaseConnectionPoolDriverName;
use Distantmagic\Resonance\JsonSchema;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

/**
 * @template-extends ConfigurationProvider<
 *     DatabaseConfiguration,
 *     array<non-empty-string, object{
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
    public function getSchema(): JsonSchema
    {
        $valueSchema = [
            'type' => 'object',
            'properties' => [
                'database' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'driver' => [
                    'type' => 'string',
                    'enum' => DatabaseConnectionPoolDriverName::values(),
                ],
                'host' => [
                    'type' => ['string', 'null'],
                    'minLength' => 1,
                    'default' => null,
                ],
                'log_queries' => [
                    'type' => 'boolean',
                ],
                'password' => [
                    'type' => 'string',
                ],
                'pool_prefill' => [
                    'type' => 'boolean',
                ],
                'pool_size' => [
                    'type' => 'integer',
                    'minimum' => 1,
                ],
                'port' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 65535,
                    'default' => 3306,
                ],
                'unix_socket' => [
                    'type' => ['string', 'null'],
                    'default' => null,
                ],
                'username' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
            ],
            'required' => [
                'database',
                'driver',
                'log_queries',
                'password',
                'pool_prefill',
                'pool_size',
                'username',
            ],
        ];

        return new JsonSchema([
            'type' => 'object',
            'additionalProperties' => $valueSchema,
        ]);
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
                    database: $connectionPoolConfiguration->database,
                    driver: DatabaseConnectionPoolDriverName::from($connectionPoolConfiguration->driver),
                    host: $connectionPoolConfiguration->host,
                    logQueries: $connectionPoolConfiguration->log_queries,
                    password: $connectionPoolConfiguration->password,
                    poolPrefill: $connectionPoolConfiguration->pool_prefill,
                    poolSize: $connectionPoolConfiguration->pool_size,
                    port: $connectionPoolConfiguration->port,
                    unixSocket: $connectionPoolConfiguration->unix_socket,
                    username: $connectionPoolConfiguration->username,
                ),
            );
        }

        return $databaseconfiguration;
    }
}
