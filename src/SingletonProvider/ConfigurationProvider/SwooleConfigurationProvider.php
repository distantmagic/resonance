<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;
use Distantmagic\Resonance\SwooleConfiguration;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

/**
 * @template-extends ConfigurationProvider<SwooleConfiguration, object{
 *     host: string,
 *     log_level: string,
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

    protected function getSchema(): Schema
    {
        return Expect::structure([
            'host' => Expect::string()->min(1)->required(),
            'log_level' => Expect::string()->min(1)->required(),
            'port' => Expect::int()->min(1)->max(65535)->required(),
            'ssl_cert_file' => Expect::string()->min(1)->required(),
            'ssl_key_file' => Expect::string()->min(1)->required(),
        ]);
    }

    protected function provideConfiguration($validatedData): SwooleConfiguration
    {
        return new SwooleConfiguration(
            host: $validatedData->host,
            logLevel: (int) ($validatedData->log_level),
            port: $validatedData->port,
            sslCertFile: $validatedData->ssl_cert_file,
            sslKeyFile: $validatedData->ssl_key_file,
        );
    }
}
