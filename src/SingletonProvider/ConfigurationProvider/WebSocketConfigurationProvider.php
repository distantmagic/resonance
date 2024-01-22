<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\JsonSchema;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;
use Distantmagic\Resonance\WebSocketConfiguration;

/**
 * @template-extends ConfigurationProvider<WebSocketConfiguration, object{
 *     max_connections: int,
 * }>
 */
#[Singleton(
    grantsFeature: Feature::WebSocket,
    provides: WebSocketConfiguration::class,
)]
final readonly class WebSocketConfigurationProvider extends ConfigurationProvider
{
    protected function getConfigurationKey(): string
    {
        return 'swoole';
    }

    protected function makeSchema(): JsonSchema
    {
        return new JsonSchema([
            'type' => 'object',
            'properties' => [
                'max_connections' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 65535,
                    'default' => 10000,
                ],
            ],
        ]);
    }

    protected function provideConfiguration($validatedData): WebSocketConfiguration
    {
        return new WebSocketConfiguration(
            maxConnections: $validatedData->max_connections,
        );
    }
}
