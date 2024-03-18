<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\Constraint\IntegerConstraint;
use Distantmagic\Resonance\Constraint\ObjectConstraint;
use Distantmagic\Resonance\ObservableTaskConfiguration;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

/**
 * @template-extends ConfigurationProvider<ObservableTaskConfiguration, array{
 *     max_tasks: int,
 *     serialized_status_size: int,
 * }>
 */
#[Singleton(provides: ObservableTaskConfiguration::class)]
final readonly class ObservableTaskConfigurationProvider extends ConfigurationProvider
{
    public function getConstraint(): Constraint
    {
        return new ObjectConstraint(
            properties: [
                'max_tasks' => (new IntegerConstraint())->default(10000),
                'serialized_status_size' => (new IntegerConstraint())->default(32768),
            ],
        );
    }

    protected function getConfigurationKey(): string
    {
        return 'observable_task';
    }

    protected function provideConfiguration($validatedData): ObservableTaskConfiguration
    {
        return new ObservableTaskConfiguration(
            maxTasks: $validatedData['max_tasks'],
            serializedStatusSize: $validatedData['serialized_status_size'],
        );
    }
}
