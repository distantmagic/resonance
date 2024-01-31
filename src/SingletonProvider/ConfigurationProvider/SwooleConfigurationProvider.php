<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\JsonSchema;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;
use Distantmagic\Resonance\SwooleConfiguration;

/**
 * @template-extends ConfigurationProvider<SwooleConfiguration, object{
 *     host: non-empty-string,
 *     log_level: non-empty-string,
 *     log_requests: boolean,
 *     port: int,
 *     ssl_cert_file: non-empty-string,
 *     ssl_key_file: non-empty-string,
 *     task_worker_num: int,
 * }>
 */
#[Singleton(provides: SwooleConfiguration::class)]
final readonly class SwooleConfigurationProvider extends ConfigurationProvider
{
    public function getSchema(): JsonSchema
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
                'task_worker_num' => [
                    'type' => 'integer',
                    'min' => 1,
                    'default' => 4,
                ],
            ],
            'required' => [
                'host',
                'log_level',
                'port',
                'ssl_cert_file',
                'ssl_key_file',
            ],
        ]);
    }

    protected function getConfigurationKey(): string
    {
        return 'swoole';
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
            taskWorkerNum: $validatedData->task_worker_num,
        );
    }
}
