<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\Constraint\BooleanConstraint;
use Distantmagic\Resonance\Constraint\IntegerConstraint;
use Distantmagic\Resonance\Constraint\MapConstraint;
use Distantmagic\Resonance\Constraint\ObjectConstraint;
use Distantmagic\Resonance\Constraint\StringConstraint;
use Distantmagic\Resonance\RedisConfiguration;
use Distantmagic\Resonance\RedisConnectionPoolConfiguration;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

/**
 * @template-extends ConfigurationProvider<
 *     RedisConfiguration,
 *     array<string, array{
 *         db_index: int,
 *         host: non-empty-string,
 *         password: null|string,
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
    public function getConstraint(): Constraint
    {
        $valueConstraint = new ObjectConstraint(
            properties: [
                'db_index' => new IntegerConstraint(),
                'host' => new StringConstraint(),
                'password' => (new StringConstraint())->nullable(),
                'pool_prefill' => (new BooleanConstraint())->default(true),
                'pool_size' => new IntegerConstraint(),
                'port' => new IntegerConstraint(),
                'prefix' => new StringConstraint(),
                'timeout' => new IntegerConstraint(),
            ]
        );

        return new MapConstraint(valueConstraint: $valueConstraint);
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
                    dbIndex: $connectionPoolConfiguration['db_index'],
                    host: $connectionPoolConfiguration['host'],
                    password: (string) $connectionPoolConfiguration['password'],
                    poolSize: $connectionPoolConfiguration['pool_size'],
                    port: $connectionPoolConfiguration['port'],
                    prefix: $connectionPoolConfiguration['prefix'],
                    timeout: $connectionPoolConfiguration['timeout'],
                ),
            );
        }

        return $databaseconfiguration;
    }
}
