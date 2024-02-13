<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\Constraint\FilenameConstraint;
use Distantmagic\Resonance\Constraint\ObjectConstraint;
use Distantmagic\Resonance\GrpcConfiguration;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

/**
 * @template-extends ConfigurationProvider<GrpcConfiguration, array{
 *     env: string,
 *     esbuild_metafile: non-empty-string,
 *     scheme: non-empty-string,
 *     url: non-empty-string,
 * }>
 */
#[Singleton(provides: GrpcConfiguration::class)]
final readonly class GrpcConfigurationProvider extends ConfigurationProvider
{
    public function getConstraint(): Constraint
    {
        return new ObjectConstraint(
            properties: [
                'grpc_php_plugin_bin' => new FilenameConstraint(),
                'protoc_bin' => new FilenameConstraint(),
            ],
        );
    }

    protected function getConfigurationKey(): string
    {
        return 'grpc';
    }

    protected function provideConfiguration($validatedData): GrpcConfiguration
    {
        return new GrpcConfiguration(
        );
    }
}
