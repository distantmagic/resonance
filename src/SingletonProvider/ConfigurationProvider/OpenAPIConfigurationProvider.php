<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\Constraint\ObjectConstraint;
use Distantmagic\Resonance\Constraint\StringConstraint;
use Distantmagic\Resonance\OpenAPIConfiguration;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

/**
 * @template-extends ConfigurationProvider<OpenAPIConfiguration, array{
 *     description: non-empty-string,
 *     title: non-empty-string,
 *     version: non-empty-string,
 * }>
 */
#[Singleton(provides: OpenAPIConfiguration::class)]
final readonly class OpenAPIConfigurationProvider extends ConfigurationProvider
{
    public function getConstraint(): Constraint
    {
        return new ObjectConstraint(
            properties: [
                'description' => new StringConstraint(),
                'title' => new StringConstraint(),
                'version' => new StringConstraint(),
            ],
        );
    }

    protected function getConfigurationKey(): string
    {
        return 'openapi';
    }

    protected function provideConfiguration($validatedData): OpenAPIConfiguration
    {
        return new OpenAPIConfiguration(
            description: $validatedData['description'],
            title: $validatedData['title'],
            version: $validatedData['version'],
        );
    }
}
