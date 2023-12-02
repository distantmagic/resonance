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
 *     scheme: string,
 *     url: string,
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
            'scheme' => Expect::anyOf('http', 'https')->default('https'),
            'url' => Expect::string()->required()->assert($this->isUrl(...)),
        ]);
    }

    protected function provideConfiguration($validatedData): ApplicationConfiguration
    {
        return new ApplicationConfiguration(
            environment: Environment::from($validatedData->env),
            esbuildMetafile: DM_ROOT.'/'.$validatedData->esbuild_metafile,
            scheme: $validatedData->scheme,
            url: rtrim($validatedData->url, '/'),
        );
    }

    private function isUrl(string $pattern): bool
    {
        // if a user puts here something like gopher://abc it's on them
        return false !== filter_var($pattern, FILTER_VALIDATE_URL);
    }
}
