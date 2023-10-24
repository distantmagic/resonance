<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;
use Distantmagic\Resonance\StaticPageConfiguration;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

/**
 * @template-extends ConfigurationProvider<StaticPageConfiguration, object{
 *     base_url: string,
 *     esbuild_metafile: string,
 *     input_directory: string,
 *     output_directory: string,
 *     sitemap: string,
 * }>
 */
#[Singleton(provides: StaticPageConfiguration::class)]
final readonly class StaticPageConfigurationProvider extends ConfigurationProvider
{
    protected function getConfigurationKey(): string
    {
        return 'static';
    }

    protected function getSchema(): Schema
    {
        return Expect::structure([
            'base_url' => Expect::string()->min(1)->required(),
            'esbuild_metafile' => Expect::string()->min(1)->required(),
            'input_directory' => Expect::string()->min(1)->required(),
            'output_directory' => Expect::string()->min(1)->required(),
            'sitemap' => Expect::string()->min(1)->required(),
        ]);
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
