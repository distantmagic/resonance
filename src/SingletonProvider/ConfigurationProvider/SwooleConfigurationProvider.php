<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\JsonSchema;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;
use Distantmagic\Resonance\SwooleConfiguration;

/**
 * @template-extends ConfigurationProvider<SwooleConfiguration, object{
 *     host: string,
 *     log_level: string,
 *     log_requests: boolean,
 *     port: int,
 *     ssl_cert_file: string,
 *     ssl_key_file: string,
 * }>
 */
#[Singleton(provides: SwooleConfiguration::class)]
final readonly class SwooleConfigurationProvider extends ConfigurationProvider
{
    protected function getConfigurationKey(): string
    {
        return 'swoole';
    }

    protected function makeSchema(): JsonSchema
    {
        return new JsonSchema([
            'type' => 'object',
            'properties' => [
                'host' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'log_level' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'log_requests' => [
                    'type' => 'boolean',
                    'default' => false,
                ],
                'port' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 65535,
                ],
                'ssl_cert_file' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'ssl_key_file' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
            ],
            'required' => ['host', 'log_level', 'port', 'ssl_cert_file', 'ssl_key_file'],
        ]);
    }

    protected function provideConfiguration($validatedData): SwooleConfiguration
    {
        return new SwooleConfiguration(
            host: $validatedData->host,
            logLevel: (int) ($validatedData->log_level),
            logRequests: $validatedData->log_requests,
            port: $validatedData->port,
            sslCertFile: $validatedData->ssl_cert_file,
            sslKeyFile: $validatedData->ssl_key_file,
        );
    }
}
