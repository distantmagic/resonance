<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\ApplicationConfiguration;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Environment;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

/**
 * @template-extends ConfigurationProvider<ApplicationConfiguration, object{
 *     env: string,
 *     esbuild_metafile: string,
 * }>
 */
#[Singleton(provides: ApplicationConfiguration::class)]
final readonly class ApplicationConfigurationProvider extends ConfigurationProvider
{
    protected function getConfigurationKey(): string
    {
        return 'app';
    }

    protected function getSchema(): Schema
    {
        return Expect::structure([
            'env' => Expect::anyOf(...Environment::values())->required(),
            'esbuild_metafile' => Expect::string()->min(1)->default('esbuild-meta.json'),
        ]);
    }

    protected function provideConfiguration($validatedData): ApplicationConfiguration
    {
        return new ApplicationConfiguration(
            environment: Environment::from($validatedData->env),
            esbuildMetafile: DM_ROOT.'/'.$validatedData->esbuild_metafile,
        );
    }
}
