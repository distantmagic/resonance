<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;
use Distantmagic\Resonance\TranslatorConfiguration;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

/**
 * @template-extends ConfigurationProvider<TranslatorConfiguration, object{
 *     base_directory: string,
 *     default_primary_language: string,
 * }>
 */
#[Singleton(provides: TranslatorConfiguration::class)]
final readonly class TranslatorConfigurationProvider extends ConfigurationProvider
{
    protected function getConfigurationKey(): string
    {
        return 'translator';
    }

    protected function getSchema(): Schema
    {
        return Expect::structure([
            'base_directory' => Expect::string()->min(1)->required(),
            'default_primary_language' => Expect::string()->min(1)->required(),
        ]);
    }

    protected function provideConfiguration($validatedData): TranslatorConfiguration
    {
        return new TranslatorConfiguration(
            baseDirectory: $validatedData->base_directory,
            defaultPrimaryLanguage: $validatedData->default_primary_language,
        );
    }
}
