<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\Constraint\FilenameConstraint;
use Distantmagic\Resonance\Constraint\ObjectConstraint;
use Distantmagic\Resonance\Constraint\StringConstraint;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;
use Distantmagic\Resonance\StaticPageConfiguration;

/**
 * @template-extends ConfigurationProvider<StaticPageConfiguration, array{
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
    public function getConstraint(): Constraint
    {
        return new ObjectConstraint(
            properties: [
                'base_url' => new StringConstraint(),
                'esbuild_metafile' => new FilenameConstraint(),
                'input_directory' => new FilenameConstraint(),
                'output_directory' => new FilenameConstraint(),
                'sitemap' => new StringConstraint(),
            ]
        );
    }

    protected function getConfigurationKey(): string
    {
        return 'static';
    }

    protected function provideConfiguration($validatedData): StaticPageConfiguration
    {
        return new StaticPageConfiguration(
            baseUrl: $validatedData['base_url'],
            esbuildMetafile: $validatedData['esbuild_metafile'],
            inputDirectory: $validatedData['input_directory'],
            outputDirectory: $validatedData['output_directory'],
            sitemap: $validatedData['sitemap'],
            stripOutputPrefix: $validatedData['output_directory'].'/',
        );
    }
}
