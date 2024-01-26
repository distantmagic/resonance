<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\JsonSchema;
use Distantmagic\Resonance\OpenAPIConfiguration;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

/**
 * @template-extends ConfigurationProvider<OpenAPIConfiguration, object{
 *     description: non-empty-string,
 *     title: non-empty-string,
 *     version: non-empty-string,
 * }>
 */
#[Singleton(provides: OpenAPIConfiguration::class)]
final readonly class OpenAPIConfigurationProvider extends ConfigurationProvider
{
    public function getSchema(): JsonSchema
    {
        return new JsonSchema([
            'type' => 'object',
            'properties' => [
                'description' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'title' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'version' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
            ],
            'required' => ['description', 'title', 'version'],
        ]);
    }

    protected function getConfigurationKey(): string
    {
        return 'openapi';
    }

    protected function provideConfiguration($validatedData): OpenAPIConfiguration
    {
        return new OpenAPIConfiguration(
            description: $validatedData->description,
            title: $validatedData->title,
            version: $validatedData->version,
        );
    }
}
