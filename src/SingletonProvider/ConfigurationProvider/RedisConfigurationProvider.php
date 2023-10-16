<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\RedisConfiguration;
use Distantmagic\Resonance\RedisConnectionPoolConfiguration;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

/**
 * @template-extends ConfigurationProvider<
 *     RedisConfiguration,
 *     array<string, object{
 *         db_index: int,
 *         host: string,
 *         password: string,
 *         port: int,
 *         prefix: string,
 *         timeout: int,
 *     }>
 * >
 */
#[Singleton(provides: RedisConfiguration::class)]
final readonly class RedisConfigurationProvider extends ConfigurationProvider
{
    protected function getConfigurationKey(): string
    {
        return 'redis';
    }

    protected function getSchema(): Schema
    {
        $keySchema = Expect::string()->min(1)->required();

        $valueSchema = Expect::structure([
            'db_index' => Expect::int()->min(0)->required(),
            'host' => Expect::string()->min(1)->required(),
            'password' => Expect::string()->required(),
            'pool_size' => Expect::int()->min(1)->required(),
            'port' => Expect::int()->min(1)->max(65535)->required(),
            'prefix' => Expect::string()->min(1)->required(),
            'timeout' => Expect::int()->min(0)->required(),
        ]);

        return Expect::arrayOf($valueSchema, $keySchema);
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
