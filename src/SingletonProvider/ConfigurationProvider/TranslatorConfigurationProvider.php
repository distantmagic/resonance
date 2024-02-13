<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\Constraint\FilenameConstraint;
use Distantmagic\Resonance\Constraint\ObjectConstraint;
use Distantmagic\Resonance\Constraint\StringConstraint;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;
use Distantmagic\Resonance\TranslatorConfiguration;

/**
 * @template-extends ConfigurationProvider<TranslatorConfiguration, array{
 *     base_directory: non-empty-string,
 *     default_primary_language: non-empty-string,
 * }>
 */
#[Singleton(provides: TranslatorConfiguration::class)]
final readonly class TranslatorConfigurationProvider extends ConfigurationProvider
{
    public function getConstraint(): Constraint
    {
        return new ObjectConstraint(
            properties: [
                'base_directory' => new FilenameConstraint(isDirectory: true),
                'default_primary_language' => new StringConstraint(),
            ]
        );
    }

    protected function getConfigurationKey(): string
    {
        return 'translator';
    }

    protected function provideConfiguration($validatedData): TranslatorConfiguration
    {
        return new TranslatorConfiguration(
            baseDirectory: $validatedData['base_directory'],
            defaultPrimaryLanguage: $validatedData['default_primary_language'],
        );
    }
}
