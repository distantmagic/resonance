<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\ConfigurationFile;
use Distantmagic\Resonance\JsonSchema;
use Distantmagic\Resonance\JsonSchemaValidator;
use Distantmagic\Resonance\RedisConfiguration;
use Distantmagic\Resonance\SessionConfiguration;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

/**
 * @template-extends ConfigurationProvider<SessionConfiguration, object{
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
        private JsonSchemaValidator $jsonSchemaValidator,
        private RedisConfiguration $redisConfiguration,
    ) {
        parent::__construct($configurationFile, $jsonSchemaValidator);
    }

    public function getSchema(): JsonSchema
    {
        $redisConnectionPools = $this
            ->redisConfiguration
            ->connectionPoolConfiguration
            ->keys()
            ->toArray()
        ;

        return new JsonSchema([
            'type' => 'object',
            'properties' => [
                'cookie_lifespan' => [
                    'type' => 'integer',
                    'minimum' => 1,
                ],
                'cookie_name' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'cookie_samesite' => [
                    'type' => 'string',
                    'enum' => ['lax', 'none', 'strict'],
                    'default' => 'lax',
                ],
                'redis_connection_pool' => [
                    'type' => 'string',
                    'enum' => $redisConnectionPools,
                ],
            ],
            'required' => ['cookie_lifespan', 'cookie_name'],
        ]);
    }

    protected function getConfigurationKey(): string
    {
        return 'session';
    }

    protected function provideConfiguration($validatedData): SessionConfiguration
    {
        return new SessionConfiguration(
            cookieLifespan: $validatedData->cookie_lifespan,
            cookieName: $validatedData->cookie_name,
            cookieSameSite: $validatedData->cookie_samesite,
            redisConnectionPool: $validatedData->redis_connection_pool,
        );
    }
}
