<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;
use Distantmagic\Resonance\StaticPageConfiguration;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

/**
 * @template-extends ConfigurationProvider<StaticPageConfiguration, object{
 *     base_url: string,
 * }>
 */
#[Singleton(provides: StaticPageConfiguration::class)]
final readonly class StaticPageConfigurationProvider extends ConfigurationProvider
{
    protected function getConfigurationKey(): string
    {
        return 'static';
    }

    protected function getSchema(): Schema
    {
        return Expect::structure([
            'base_url' => Expect::string()->min(1)->required(),
        ]);
    }

    protected function provideConfiguration(object $validatedData): StaticPageConfiguration
    {
        return new StaticPageConfiguration(
            baseUrl: $validatedData->base_url,
        );
    }
}
