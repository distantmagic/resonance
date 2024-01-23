<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\JsonSchema;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;
use Distantmagic\Resonance\TranslatorConfiguration;

/**
 * @template-extends ConfigurationProvider<TranslatorConfiguration, object{
 *     base_directory: string,
 *     default_primary_language: string,
 * }>
 */
#[Singleton(provides: TranslatorConfiguration::class)]
final readonly class TranslatorConfigurationProvider extends ConfigurationProvider
{
    public function getSchema(): JsonSchema
    {
        return new JsonSchema([
            'type' => 'object',
            'properties' => [
                'base_directory' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'default_primary_language' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
            ],
            'required' => ['base_directory', 'default_primary_language'],
        ]);
    }

    protected function getConfigurationKey(): string
    {
        return 'translator';
    }

    protected function provideConfiguration($validatedData): TranslatorConfiguration
    {
        return new TranslatorConfiguration(
            baseDirectory: $validatedData->base_directory,
            defaultPrimaryLanguage: $validatedData->default_primary_language,
        );
    }
}
