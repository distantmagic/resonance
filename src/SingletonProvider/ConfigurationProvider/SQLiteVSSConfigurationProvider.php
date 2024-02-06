<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\Constraint\ObjectConstraint;
use Distantmagic\Resonance\Constraint\StringConstraint;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;
use Distantmagic\Resonance\SQLiteVSSConfiguration;

/**
 * @template-extends ConfigurationProvider<SQLiteVSSConfiguration, array{
 *     extension_vector0: non-empty-string,
 *     extension_vss0: non-empty-string,
 * }>
 */
#[Singleton(provides: SQLiteVSSConfiguration::class)]
final readonly class SQLiteVSSConfigurationProvider extends ConfigurationProvider
{
    public function getConstraint(): Constraint
    {
        return new ObjectConstraint(
            properties: [
                'extension_vector0' => new StringConstraint(),
                'extension_vss0' => new StringConstraint(),
            ]
        );
    }

    protected function getConfigurationKey(): string
    {
        return 'sqlite-vss';
    }

    protected function provideConfiguration($validatedData): SQLiteVSSConfiguration
    {
        return new SQLiteVSSConfiguration(
            extensionVector0: $validatedData['extension_vector0'],
            extensionVss0: $validatedData['extension_vss0'],
        );
    }
}
