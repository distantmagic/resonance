<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\JsonSchema;
use Distantmagic\Resonance\MailerConfiguration;
use Distantmagic\Resonance\MailerTransportConfiguration;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

/**
 * @template-extends ConfigurationProvider<
 *     MailerConfiguration,
 *     array<non-empty-string, object{
 *         dkim_domain_name: null|non-empty-string,
 *         dkim_selector: null|non-empty-string,
 *         dkim_signing_key_passphrase: null|non-empty-string,
 *         dkim_signing_key_private: null|non-empty-string,
 *         dkim_signing_key_public: null|non-empty-string,
 *         transport_dsn: non-empty-string,
 *     }>
 * >
 */
#[Singleton(provides: MailerConfiguration::class)]
final readonly class MailerConfigurationProvider extends ConfigurationProvider
{
    public function getSchema(): JsonSchema
    {
        $valueSchema = [
            'type' => 'object',
            'properties' => [
                'dkim_domain_name' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'default' => null,
                ],
                'dkim_selector' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'default' => null,
                ],
                'dkim_signing_key_passphrase' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'default' => null,
                ],
                'dkim_signing_key_private' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'default' => null,
                ],
                'dkim_signing_key_public' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'default' => null,
                ],
                'transport_dsn' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
            ],
            'required' => [
                'transport_dsn',
            ],
        ];

        return new JsonSchema([
            'type' => 'object',
            'additionalProperties' => $valueSchema,
        ]);
    }

    protected function getConfigurationKey(): string
    {
        return 'mailer';
    }

    protected function provideConfiguration($validatedData): MailerConfiguration
    {
        $mailerConfiguration = new MailerConfiguration();

        foreach ($validatedData as $name => $transportConfiguration) {
            $mailerConfiguration->transportConfiguration->put(
                $name,
                new MailerTransportConfiguration(
                    dkimDomainName: $transportConfiguration->dkim_domain_name,
                    dkimSelector: $transportConfiguration->dkim_selector,
                    dkimSigningKeyPassphrase: $transportConfiguration->dkim_signing_key_passphrase,
                    dkimSigningKeyPrivate: $transportConfiguration->dkim_signing_key_private,
                    dkimSigningKeyPublic: $transportConfiguration->dkim_signing_key_public,
                    transportDsn: $transportConfiguration->transport_dsn,
                )
            );
        }

        return $mailerConfiguration;
    }
}
