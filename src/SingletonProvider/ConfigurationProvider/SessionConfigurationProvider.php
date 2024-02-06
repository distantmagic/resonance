<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\ConfigurationFile;
use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\Constraint\EnumConstraint;
use Distantmagic\Resonance\Constraint\IntegerConstraint;
use Distantmagic\Resonance\Constraint\ObjectConstraint;
use Distantmagic\Resonance\Constraint\StringConstraint;
use Distantmagic\Resonance\RedisConfiguration;
use Distantmagic\Resonance\SessionConfiguration;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

/**
 * @template-extends ConfigurationProvider<SessionConfiguration, array{
 *     cookie_lifespan: int,
 *     cookie_name: non-empty-string,
 *     cookie_samesite: string,
 *     redis_connection_pool: string,
 * }>
 */
#[Singleton(provides: SessionConfiguration::class)]
final readonly class SessionConfigurationProvider extends ConfigurationProvider
{
    public function __construct(
        private ConfigurationFile $configurationFile,
        private RedisConfiguration $redisConfiguration,
    ) {
        parent::__construct($configurationFile);
    }

    public function getConstraint(): Constraint
    {
        $redisConnectionPools = $this
            ->redisConfiguration
            ->connectionPoolConfiguration
            ->keys()
            ->toArray()
        ;

        return new ObjectConstraint([
            'cookie_lifespan' => new IntegerConstraint(),
            'cookie_name' => new StringConstraint(),
            'cookie_samesite' => (new EnumConstraint(['lax', 'none', 'strict']))->default('lax'),
            'redis_connection_pool' => new EnumConstraint($redisConnectionPools),
        ]);
    }

    protected function getConfigurationKey(): string
    {
        return 'session';
    }

    protected function provideConfiguration($validatedData): SessionConfiguration
    {
        return new SessionConfiguration(
            cookieLifespan: $validatedData['cookie_lifespan'],
            cookieName: $validatedData['cookie_name'],
            cookieSameSite: $validatedData['cookie_samesite'],
            redisConnectionPool: $validatedData['redis_connection_pool'],
        );
    }
}
