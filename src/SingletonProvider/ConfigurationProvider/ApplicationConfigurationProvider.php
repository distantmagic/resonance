<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\ApplicationConfiguration;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Environment;
use Distantmagic\Resonance\JsonSchema;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;
use RuntimeException;

/**
 * @template-extends ConfigurationProvider<ApplicationConfiguration, object{
 *     env: string,
 *     esbuild_metafile: non-empty-string,
 *     scheme: non-empty-string,
 *     url: non-empty-string,
 * }>
 */
#[Singleton(provides: ApplicationConfiguration::class)]
final readonly class ApplicationConfigurationProvider extends ConfigurationProvider
{
    public function getSchema(): JsonSchema
    {
        return new JsonSchema([
            'type' => 'object',
            'properties' => [
                'env' => [
                    'type' => 'string',
                    'enum' => Environment::values(),
                ],
                'esbuild_metafile' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'default' => 'esbuild-meta.json',
                ],
                'scheme' => [
                    'type' => 'string',
                    'enum' => ['http', 'https'],
                    'default' => 'https',
                ],
                'url' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'format' => 'uri',
                ],
            ],
            'required' => ['env', 'url'],
        ]);
    }

    protected function getConfigurationKey(): string
    {
        return 'app';
    }

    protected function provideConfiguration($validatedData): ApplicationConfiguration
    {
        $url = rtrim($validatedData->url, '/');

        if (empty($url)) {
            throw new RuntimeException('URL cannot be an empty string');
        }

        return new ApplicationConfiguration(
            environment: Environment::from($validatedData->env),
            esbuildMetafile: DM_ROOT.'/'.$validatedData->esbuild_metafile,
            scheme: $validatedData->scheme,
            url: $url,
        );
    }
}
