<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Defuse\Crypto\Key;
use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Constraint;
use Distantmagic\Resonance\Constraint\ObjectConstraint;
use Distantmagic\Resonance\Constraint\StringConstraint;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\OAuth2Configuration;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;
use League\OAuth2\Server\CryptKey;
use RuntimeException;
use Swoole\Coroutine;

/**
 * @template-extends ConfigurationProvider<OAuth2Configuration, array{
 *     encryption_key: non-empty-string,
 *     jwt_signing_key_passphrase: null|string,
 *     jwt_signing_key_private: non-empty-string,
 *     jwt_signing_key_public: non-empty-string,
 *     session_key_authorization_request: non-empty-string,
 *     session_key_pkce: non-empty-string,
 *     session_key_state: non-empty-string,
 * }>
 */
#[GrantsFeature(Feature::OAuth2)]
#[Singleton(provides: OAuth2Configuration::class)]
final readonly class OAuth2ConfigurationProvider extends ConfigurationProvider
{
    public function getConstraint(): Constraint
    {
        return new ObjectConstraint([
            'encryption_key' => new StringConstraint(),
            'jwt_signing_key_passphrase' => (new StringConstraint())->nullable(),
            'jwt_signing_key_private' => new StringConstraint(),
            'jwt_signing_key_public' => new StringConstraint(),
            'session_key_authorization_request' => (new StringConstraint())->default('oauth2.authorization_request'),
            'session_key_pkce' => (new StringConstraint())->default('oauth2.pkce'),
            'session_key_state' => (new StringConstraint())->default('oauth2.state'),
        ]);
    }

    protected function getConfigurationKey(): string
    {
        return 'oauth2';
    }

    protected function provideConfiguration($validatedData): OAuth2Configuration
    {
        $encryptionKeyContent = Coroutine::readFile($validatedData['encryption_key']);

        if (!is_string($encryptionKeyContent)) {
            throw new RuntimeException('Unable to read encrpytion key file: '.$validatedData['encryption_key']);
        }

        return new OAuth2Configuration(
            encryptionKey: Key::loadFromAsciiSafeString($encryptionKeyContent),
            jwtSigningKeyPrivate: new CryptKey(
                DM_ROOT.'/'.$validatedData['jwt_signing_key_private'],
                $validatedData['jwt_signing_key_passphrase'],
            ),
            jwtSigningKeyPublic: new CryptKey(
                DM_ROOT.'/'.$validatedData['jwt_signing_key_public'],
                $validatedData['jwt_signing_key_passphrase'],
            ),
            sessionKeyAuthorizationRequest: $validatedData['session_key_authorization_request'],
            sessionKeyPkce: $validatedData['session_key_pkce'],
            sessionKeyState: $validatedData['session_key_state'],
        );
    }
}
