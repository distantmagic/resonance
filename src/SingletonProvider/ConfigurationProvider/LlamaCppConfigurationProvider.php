<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\JsonSchema;
use Distantmagic\Resonance\LlamaCppConfiguration;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

/**
 * @template-extends ConfigurationProvider<LlamaCppConfiguration, object{
 *     api_key: null|non-empty-string,
 *     completion_token_timeout: float,
 *     host: non-empty-string,
 *     port: int,
 *     scheme: non-empty-string,
 * }>
 */
#[Singleton(provides: LlamaCppConfiguration::class)]
final readonly class LlamaCppConfigurationProvider extends ConfigurationProvider
{
    public function getSchema(): JsonSchema
    {
        return new JsonSchema([
            'type' => 'object',
            'properties' => [
                'api_key' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'default' => null,
                ],
                'host' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'completion_token_timeout' => [
                    'type' => 'number',
                    'default' => 1.0,
                ],
                'port' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 65535,
                ],
                'scheme' => [
                    'type' => 'string',
                    'enum' => ['http', 'https'],
                    'default' => 'http',
                ],
            ],
            'required' => ['host', 'port'],
        ]);
    }

    protected function getConfigurationKey(): string
    {
        return 'llamacpp';
    }

    protected function provideConfiguration($validatedData): LlamaCppConfiguration
    {
        return new LlamaCppConfiguration(
            apiKey: $validatedData->api_key,
            completionTokenTimeout: $validatedData->completion_token_timeout,
            host: $validatedData->host,
            port: $validatedData->port,
            scheme: $validatedData->scheme,
        );
    }
}
