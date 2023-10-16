<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\ConfigurationFile;
use Distantmagic\Resonance\RedisConfiguration;
use Distantmagic\Resonance\SessionConfiguration;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;
use Nette\Schema\Expect;
use Nette\Schema\Processor;
use Nette\Schema\Schema;

/**
 * @template-extends ConfigurationProvider<SessionConfiguration, object{
 *     cookie_lifespan: int,
 *     cookie_name: string,
 *     redis_connection_pool: string,
 * }>
 */
#[Singleton(provides: SessionConfiguration::class)]
final readonly class SessionConfigurationProvider extends ConfigurationProvider
{
    public function __construct(
        private ConfigurationFile $configurationFile,
        private Processor $processor,
        private RedisConfiguration $redisConfiguration,
    ) {
        parent::__construct($configurationFile, $processor);
    }

    protected function getConfigurationKey(): string
    {
        return 'session';
    }

    protected function getSchema(): Schema
    {
        $redisConnectionPools = $this
            ->redisConfiguration
            ->connectionPoolConfiguration
            ->keys()
            ->toArray()
        ;

        return Expect::structure([
            'cookie_lifespan' => Expect::int()->min(1)->required(),
            'cookie_name' => Expect::string()->min(1)->required(),
            'redis_connection_pool' => Expect::anyOf(...$redisConnectionPools),
        ]);
    }

    protected function provideConfiguration($validatedData): SessionConfiguration
    {
        return new SessionConfiguration(
            cookieLifespan: $validatedData->cookie_lifespan,
            cookieName: $validatedData->cookie_name,
            redisConnectionPool: $validatedData->redis_connection_pool,
        );
    }
}
