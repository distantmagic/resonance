<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\OpenAPIConfiguration;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

/**
 * @template-extends ConfigurationProvider<OpenAPIConfiguration, object{
 *     description: string,
 *     title: string,
 *     version: string,
 * }>
 */
#[Singleton(provides: OpenAPIConfiguration::class)]
final readonly class OpenAPIConfigurationProvider extends ConfigurationProvider
{
    protected function getConfigurationKey(): string
    {
        return 'openapi';
    }

    protected function getSchema(): Schema
    {
        return Expect::structure([
            'description' => Expect::string()->min(1)->required(),
            'title' => Expect::string()->min(1)->required(),
            'version' => Expect::string()->min(1)->required(),
        ]);
    }

    protected function provideConfiguration($validatedData): OpenAPIConfiguration
    {
        return new OpenAPIConfiguration(
            description: $validatedData->description,
            title: $validatedData->title,
            version: $validatedData->version,
        );
    }
}
