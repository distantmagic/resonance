<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\JsonSchema;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;
use Distantmagic\Resonance\StaticPageConfiguration;

/**
 * @template-extends ConfigurationProvider<StaticPageConfiguration, object{
 *     base_url: non-empty-string,
 *     esbuild_metafile: non-empty-string,
 *     input_directory: non-empty-string,
 *     output_directory: non-empty-string,
 *     sitemap: non-empty-string,
 * }>
 */
#[Singleton(provides: StaticPageConfiguration::class)]
final readonly class StaticPageConfigurationProvider extends ConfigurationProvider
{
    public function getSchema(): JsonSchema
    {
        return new JsonSchema([
            'type' => 'object',
            'properties' => [
                'base_url' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'esbuild_metafile' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'input_directory' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'output_directory' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'sitemap' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
            ],
            'required' => ['base_url', 'esbuild_metafile', 'input_directory', 'output_directory', 'sitemap'],
        ]);
    }

    protected function getConfigurationKey(): string
    {
        return 'static';
    }

    protected function provideConfiguration($validatedData): StaticPageConfiguration
    {
        return new StaticPageConfiguration(
            baseUrl: $validatedData->base_url,
            esbuildMetafile: DM_ROOT.'/'.$validatedData->esbuild_metafile,
            inputDirectory: DM_ROOT.'/'.$validatedData->input_directory,
            outputDirectory: DM_ROOT.'/'.$validatedData->output_directory,
            sitemap: DM_ROOT.'/'.$validatedData->sitemap,
            stripOutputPrefix: $validatedData->output_directory.'/',
        );
    }
}
