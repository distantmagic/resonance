<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\Constraint\EnumConstraint;
use Distantmagic\Resonance\Constraint\IntegerConstraint;
use Distantmagic\Resonance\Constraint\NumberConstraint;
use Distantmagic\Resonance\Constraint\ObjectConstraint;
use Distantmagic\Resonance\Constraint\StringConstraint;
use Distantmagic\Resonance\LlamaCppConfiguration;
use Distantmagic\Resonance\LlmChatTemplateType;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

/**
 * @template-extends ConfigurationProvider<LlamaCppConfiguration, array{
 *     api_key: null|non-empty-string,
 *     chat_template: non-empty-string,
 *     completion_token_timeout: float,
 *     host: non-empty-string,
 *     port: int,
 *     scheme: non-empty-string,
 * }>
 */
#[Singleton(provides: LlamaCppConfiguration::class)]
final readonly class LlamaCppConfigurationProvider extends ConfigurationProvider
{
    public function getConstraint(): Constraint
    {
        return new ObjectConstraint(
            properties: [
                'api_key' => (new StringConstraint())->default(null),
                'chat_template' => new EnumConstraint(LlmChatTemplateType::values()),
                'completion_token_timeout' => (new NumberConstraint())->default(1.0),
                'host' => new StringConstraint(),
                'port' => new IntegerConstraint(),
                'scheme' => (new EnumConstraint(['http', 'https']))->default('http'),
            ]
        );
    }

    protected function getConfigurationKey(): string
    {
        return 'llamacpp';
    }

    protected function provideConfiguration($validatedData): LlamaCppConfiguration
    {
        return new LlamaCppConfiguration(
            apiKey: $validatedData['api_key'],
            completionTokenTimeout: $validatedData['completion_token_timeout'],
            host: $validatedData['host'],
            port: $validatedData['port'],
            llmChatTemplate: LlmChatTemplateType::from($validatedData['chat_template']),
            scheme: $validatedData['scheme'],
        );
    }
}
