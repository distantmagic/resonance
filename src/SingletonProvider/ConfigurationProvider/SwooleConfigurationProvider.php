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
 *     port: int,
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
            'port' => Expect::int()->min(1)->max(65535)->required(),
        ]);
    }

    protected function provideConfiguration(object $validatedData): SwooleConfiguration
    {
        return new SwooleConfiguration(
            host: $validatedData->host,
            port: $validatedData->port,
        );
    }
}
