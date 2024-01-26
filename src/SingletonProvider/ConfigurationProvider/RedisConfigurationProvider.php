<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\JsonSchema;
use Distantmagic\Resonance\RedisConfiguration;
use Distantmagic\Resonance\RedisConnectionPoolConfiguration;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

/**
 * @template-extends ConfigurationProvider<
 *     RedisConfiguration,
 *     array<string, object{
 *         db_index: int,
 *         host: non-empty-string,
 *         password: string,
 *         pool_prefill: bool,
 *         pool_size: int,
 *         port: int,
 *         prefix: non-empty-string,
 *         timeout: int,
 *     }>
 * >
 */
#[Singleton(provides: RedisConfiguration::class)]
final readonly class RedisConfigurationProvider extends ConfigurationProvider
{
    public function getSchema(): JsonSchema
    {
        $valueSchema = [
            'type' => 'object',
            'properties' => [
                'db_index' => [
                    'type' => 'integer',
                    'minimum' => 0,
                ],
                'host' => [
                    'type' => 'string',
                    'minLength' => 1,
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
                ],
                'prefix' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'timeout' => [
                    'type' => 'integer',
                    'minimum' => 0,
                ],
            ],
            'required' => [
                'db_index',
                'host',
                'password',
                'pool_prefill',
                'pool_size',
                'port',
                'prefix',
                'timeout',
            ],
        ];

        return new JsonSchema([
            'type' => 'object',
            'additionalProperties' => $valueSchema,
        ]);
    }

    protected function getConfigurationKey(): string
    {
        return 'redis';
    }

    protected function provideConfiguration($validatedData): RedisConfiguration
    {
        $databaseconfiguration = new RedisConfiguration();

        foreach ($validatedData as $name => $connectionPoolConfiguration) {
            $databaseconfiguration->connectionPoolConfiguration->put(
                $name,
                new RedisConnectionPoolConfiguration(
                    dbIndex: $connectionPoolConfiguration->db_index,
                    host: $connectionPoolConfiguration->host,
                    password: $connectionPoolConfiguration->password,
                    poolPrefill: $connectionPoolConfiguration->pool_prefill,
                    poolSize: $connectionPoolConfiguration->pool_size,
                    port: $connectionPoolConfiguration->port,
                    prefix: $connectionPoolConfiguration->prefix,
                    timeout: $connectionPoolConfiguration->timeout,
                ),
            );
        }

        return $databaseconfiguration;
    }
}
