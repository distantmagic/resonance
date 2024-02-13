<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\ApplicationConfiguration;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\Constraint\EnumConstraint;
use Distantmagic\Resonance\Constraint\ObjectConstraint;
use Distantmagic\Resonance\Constraint\StringConstraint;
use Distantmagic\Resonance\Environment;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;
use RuntimeException;

/**
 * @template-extends ConfigurationProvider<ApplicationConfiguration, array{
 *     env: string,
 *     esbuild_metafile: non-empty-string,
 *     scheme: non-empty-string,
 *     url: non-empty-string,
 * }>
 */
#[Singleton(provides: ApplicationConfiguration::class)]
final readonly class ApplicationConfigurationProvider extends ConfigurationProvider
{
    public function getConstraint(): Constraint
    {
        return new ObjectConstraint(
            properties: [
                'env' => new EnumConstraint(Environment::values()),
                'esbuild_metafile' => (new StringConstraint())->default('esbuild-meta.json'),
                'scheme' => (new EnumConstraint(['http', 'https']))->default('https'),
                'url' => new StringConstraint(),
            ],
        );
    }

    protected function getConfigurationKey(): string
    {
        return 'app';
    }

    protected function provideConfiguration($validatedData): ApplicationConfiguration
    {
        $url = rtrim($validatedData['url'], '/');

        if (empty($url)) {
            throw new RuntimeException('URL cannot be an empty string');
        }

        return new ApplicationConfiguration(
            environment: Environment::from($validatedData['env']),
            esbuildMetafile: $validatedData['esbuild_metafile'],
            scheme: $validatedData['scheme'],
            url: $url,
        );
    }
}
