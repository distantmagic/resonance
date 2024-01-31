<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\JsonSchema;
use Distantmagic\Resonance\MailerConfiguration;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

/**
 * @template-extends ConfigurationProvider<MailerConfiguration, object{
 *     dkim_domain_name: non-empty-string,
 *     dkim_selector: non-empty-string,
 *     dkim_signing_key_passphrase: non-empty-string,
 *     dkim_signing_key_private: non-empty-string,
 *     dkim_signing_key_public: non-empty-string,
 *     transport_dsn: non-empty-string,
 * }>
 */
#[Singleton(provides: MailerConfiguration::class)]
final readonly class MailerConfigurationProvider extends ConfigurationProvider
{
    public function getSchema(): JsonSchema
    {
        return new JsonSchema([
            'type' => 'object',
            'properties' => [
                'dkim_domain_name' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'dkim_selector' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'dkim_signing_key_passphrase' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'dkim_signing_key_private' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'dkim_signing_key_public' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'transport_dsn' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
            ],
            'required' => [
                'dkim_domain_name',
                'dkim_selector',
                'dkim_signing_key_passphrase',
                'dkim_signing_key_private',
                'dkim_signing_key_public',
                'transport_dsn',
            ],
        ]);
    }

    protected function getConfigurationKey(): string
    {
        return 'mailer';
    }

    protected function provideConfiguration($validatedData): MailerConfiguration
    {
        return new MailerConfiguration(
            dkimDomainName: $validatedData->dkim_domain_name,
            dkimSelector: $validatedData->dkim_selector,
            dkimSigningKeyPassphrase: $validatedData->dkim_signing_key_passphrase,
            dkimSigningKeyPrivate: $validatedData->dkim_signing_key_private,
            dkimSigningKeyPublic: $validatedData->dkim_signing_key_public,
            transportDsn: $validatedData->transport_dsn,
        );
    }
}
