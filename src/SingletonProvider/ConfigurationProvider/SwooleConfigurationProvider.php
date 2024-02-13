<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\Constraint\BooleanConstraint;
use Distantmagic\Resonance\Constraint\FilenameConstraint;
use Distantmagic\Resonance\Constraint\IntegerConstraint;
use Distantmagic\Resonance\Constraint\ObjectConstraint;
use Distantmagic\Resonance\Constraint\StringConstraint;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;
use Distantmagic\Resonance\SwooleConfiguration;

/**
 * @template-extends ConfigurationProvider<SwooleConfiguration, array{
 *     host: non-empty-string,
 *     log_level: int,
 *     log_requests: boolean,
 *     port: int,
 *     ssl_cert_file: null|non-empty-string,
 *     ssl_key_file: null|non-empty-string,
 *     task_worker_num: int,
 * }>
 */
#[Singleton(provides: SwooleConfiguration::class)]
final readonly class SwooleConfigurationProvider extends ConfigurationProvider
{
    public function getConstraint(): Constraint
    {
        return new ObjectConstraint(
            properties: [
                'host' => new StringConstraint(),
                'log_level' => new IntegerConstraint(),
                'log_requests' => (new BooleanConstraint())->default(false),
                'port' => new IntegerConstraint(),
                'ssl_cert_file' => (new FilenameConstraint())->nullable(),
                'ssl_key_file' => (new FilenameConstraint())->nullable(),
                'task_worker_num' => (new IntegerConstraint())->default(4),
            ],
        );
    }

    protected function getConfigurationKey(): string
    {
        return 'swoole';
    }

    protected function provideConfiguration($validatedData): SwooleConfiguration
    {
        return new SwooleConfiguration(
            host: $validatedData['host'],
            logLevel: $validatedData['log_level'],
            logRequests: $validatedData['log_requests'],
            port: $validatedData['port'],
            sslCertFile: $validatedData['ssl_cert_file'],
            sslKeyFile: $validatedData['ssl_key_file'],
            taskWorkerNum: $validatedData['task_worker_num'],
        );
    }
}
