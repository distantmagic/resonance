<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\JsonSchema;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;
use Distantmagic\Resonance\SQLiteVSSConfiguration;

/**
 * @template-extends ConfigurationProvider<SQLiteVSSConfiguration, object{
 *     extension_vector0: non-empty-string,
 *     extension_vss0: non-empty-string,
 * }>
 */
#[Singleton(provides: SQLiteVSSConfiguration::class)]
final readonly class SQLiteVSSConfigurationProvider extends ConfigurationProvider
{
    public function getSchema(): JsonSchema
    {
        return new JsonSchema([
            'type' => 'object',
            'properties' => [
                'extension_vector0' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'extension_vss0' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
            ],
            'required' => ['extension_vector0', 'extension_vss0'],
        ]);
    }

    protected function getConfigurationKey(): string
    {
        return 'sqlite-vss';
    }

    protected function provideConfiguration($validatedData): SQLiteVSSConfiguration
    {
        return new SQLiteVSSConfiguration(
            extensionVector0: $validatedData->extension_vector0,
            extensionVss0: $validatedData->extension_vss0,
        );
    }
}
