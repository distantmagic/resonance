<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\RedisConfiguration;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

/**
 * @template-extends ConfigurationProvider<RedisConfiguration, object{
 *     host: string,
 *     password: string,
 *     port: int,
 *     prefix: string,
 * }>
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
        return Expect::structure([
            'host' => Expect::string()->min(1)->required(),
            'password' => Expect::string()->required(),
            'port' => Expect::int()->min(1)->max(65535)->required(),
            'prefix' => Expect::string()->min(1)->required(),
        ]);
    }

    protected function provideConfiguration($validatedData): RedisConfiguration
    {
        return new RedisConfiguration(
            host: $validatedData->host,
            password: $validatedData->password,
            port: $validatedData->port,
            prefix: $validatedData->prefix,
        );
    }
}
