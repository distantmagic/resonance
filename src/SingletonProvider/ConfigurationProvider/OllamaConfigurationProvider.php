<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\JsonSchema;
use Distantmagic\Resonance\OllamaConfiguration;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

/**
 * @template-extends ConfigurationProvider<OllamaConfiguration, object{
 *     host: string,
 *     port: int,
 *     scheme: string,
 * }>
 */
#[Singleton(provides: OllamaConfiguration::class)]
final readonly class OllamaConfigurationProvider extends ConfigurationProvider
{
    protected function getConfigurationKey(): string
    {
        return 'ollama';
    }

    protected function makeSchema(): JsonSchema
    {
        return new JsonSchema([
            'type' => 'object',
            'properties' => [
                'host' => [
                    'type' => 'string',
                    'minLength' => 1,
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

    protected function provideConfiguration($validatedData): OllamaConfiguration
    {
        return new OllamaConfiguration(
            host: $validatedData->host,
            port: $validatedData->port,
            scheme: $validatedData->scheme,
        );
    }
}
