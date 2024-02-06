<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\Constraint\MapConstraint;
use Distantmagic\Resonance\Constraint\ObjectConstraint;
use Distantmagic\Resonance\Constraint\StringConstraint;
use Distantmagic\Resonance\MailerConfiguration;
use Distantmagic\Resonance\MailerTransportConfiguration;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

/**
 * @template-extends ConfigurationProvider<
 *     MailerConfiguration,
 *     array<non-empty-string, array{
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
    public function getConstraint(): Constraint
    {
        $valueConstraint = new ObjectConstraint([
            'dkim_domain_name' => (new StringConstraint())->nullable(),
            'dkim_selector' => (new StringConstraint())->nullable(),
            'dkim_signing_key_passphrase' => (new StringConstraint())->nullable(),
            'dkim_signing_key_private' => (new StringConstraint())->nullable(),
            'dkim_signing_key_public' => (new StringConstraint())->nullable(),
            'transport_dsn' => new StringConstraint(),
        ]);

        return new MapConstraint(valueConstraint: $valueConstraint);
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
                    dkimDomainName: $transportConfiguration['dkim_domain_name'],
                    dkimSelector: $transportConfiguration['dkim_selector'],
                    dkimSigningKeyPassphrase: $transportConfiguration['dkim_signing_key_passphrase'],
                    dkimSigningKeyPrivate: $transportConfiguration['dkim_signing_key_private'],
                    dkimSigningKeyPublic: $transportConfiguration['dkim_signing_key_public'],
                    transportDsn: $transportConfiguration['transport_dsn'],
                )
            );
        }

        return $mailerConfiguration;
    }
}
