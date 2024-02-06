<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\Constraint\IntegerConstraint;
use Distantmagic\Resonance\Constraint\ObjectConstraint;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;
use Distantmagic\Resonance\WebSocketConfiguration;

/**
 * @template-extends ConfigurationProvider<WebSocketConfiguration, array{
 *     max_connections: int,
 * }>
 */
#[GrantsFeature(Feature::WebSocket)]
#[Singleton(provides: WebSocketConfiguration::class)]
final readonly class WebSocketConfigurationProvider extends ConfigurationProvider
{
    public function getConstraint(): Constraint
    {
        return new ObjectConstraint(
            // 'minimum' => 1,
            // 'maximum' => 65535,
            // 'default' => 10000,
            properties: [
                'max_connections' => (new IntegerConstraint())->default(10000),
            ],
        );
    }

    protected function getConfigurationKey(): string
    {
        return 'swoole';
    }

    protected function provideConfiguration($validatedData): WebSocketConfiguration
    {
        return new WebSocketConfiguration(
            maxConnections: $validatedData['max_connections'],
        );
    }
}
