<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\Constraint\FilenameConstraint;
use Distantmagic\Resonance\Constraint\IntegerConstraint;
use Distantmagic\Resonance\Constraint\MapConstraint;
use Distantmagic\Resonance\Constraint\ObjectConstraint;
use Distantmagic\Resonance\Constraint\StringConstraint;
use Distantmagic\Resonance\GrpcConfiguration;
use Distantmagic\Resonance\GrpcPoolConfiguration;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

/**
 * @template-extends ConfigurationProvider<
 *     GrpcConfiguration,
 *     array<non-empty-string, array{
 *         grpc_php_plugin_bin: non-empty-string,
 *         out_directory: non-empty-string,
 *         proto_file: non-empty-string,
 *         protoc_bin: non-empty-string,
 *         protos_directory: non-empty-string,
 *         server_host: non-empty-string,
 *         server_port: int,
 *         server_request_retry_attempts: int,
 *         server_request_retry_interval: int,
 *     }>
 * >
 */
#[Singleton(provides: GrpcConfiguration::class)]
final readonly class GrpcConfigurationProvider extends ConfigurationProvider
{
    public function getConstraint(): Constraint
    {
        $valueConstraint = new ObjectConstraint(
            properties: [
                'grpc_php_plugin_bin' => new FilenameConstraint(),
                'out_directory' => new FilenameConstraint(isDirectory: true),
                'proto_file' => new FilenameConstraint(),
                'protoc_bin' => new FilenameConstraint(),
                'protos_directory' => new FilenameConstraint(isDirectory: true),
                'server_host' => new StringConstraint(),
                'server_port' => new IntegerConstraint(),
                'server_request_retry_attempts' => (new IntegerConstraint())->default(3),
                'server_request_retry_interval' => (new IntegerConstraint())->default(100),
            ],
        );

        return new MapConstraint(valueConstraint: $valueConstraint);
    }

    protected function getConfigurationKey(): string
    {
        return 'grpc';
    }

    protected function provideConfiguration($validatedData): GrpcConfiguration
    {
        $grpConfiguration = new GrpcConfiguration();

        foreach ($validatedData as $name => $poolConfiguration) {
            $grpConfiguration->poolConfiguration->put(
                $name,
                new GrpcPoolConfiguration(
                    grpcPhpPluginBin: $poolConfiguration['grpc_php_plugin_bin'],
                    outDirectory: $poolConfiguration['out_directory'],
                    protocBin: $poolConfiguration['protoc_bin'],
                    protoFile: $poolConfiguration['proto_file'],
                    protosDirectory: $poolConfiguration['protos_directory'],
                    serverHost: $poolConfiguration['server_host'],
                    serverPort: $poolConfiguration['server_port'],
                    serverRequestRetryAttempts: $poolConfiguration['server_request_retry_attempts'],
                    serverRequestRetryInterval: $poolConfiguration['server_request_retry_interval'],
                ),
            );
        }

        return $grpConfiguration;
    }
}
