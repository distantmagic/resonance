<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;

use Defuse\Crypto\Key;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\OAuth2Configuration;
use Distantmagic\Resonance\SingletonProvider\ConfigurationProvider;
use League\OAuth2\Server\CryptKey;
use Nette\Schema\Expect;
use Nette\Schema\Schema;

/**
 * @template-extends ConfigurationProvider<OAuth2Configuration, object{
 *     encryption_key: string,
 *     jwt_signing_key_passphrase: null|string,
 *     jwt_signing_key_private: string,
 *     jwt_signing_key_public: string,
 *     session_key_authorization_request: string,
 *     session_key_pkce: string,
 *     session_key_state: string,
 * }>
 */
#[Singleton(provides: OAuth2Configuration::class)]
final readonly class OAuth2ConfigurationProvider extends ConfigurationProvider
{
    protected function getConfigurationKey(): string
    {
        return 'oauth2';
    }

    protected function getSchema(): Schema
    {
        return Expect::structure([
            'encryption_key' => Expect::string()->min(1)->required(),
            'jwt_signing_key_passphrase' => Expect::string()->default(null),
            'jwt_signing_key_private' => Expect::string()->min(1)->required(),
            'jwt_signing_key_public' => Expect::string()->min(1)->required(),
            'session_key_authorization_request' => Expect::string()->min(1)->default('oauth2.authorization_request'),
            'session_key_pkce' => Expect::string()->min(1)->default('oauth2.pkce'),
            'session_key_state' => Expect::string()->min(1)->default('oauth2.state'),
        ]);
    }

    protected function provideConfiguration($validatedData): OAuth2Configuration
    {
        return new OAuth2Configuration(
            encryptionKey: Key::loadFromAsciiSafeString(file_get_contents($validatedData->encryption_key)),
            jwtSigningKeyPrivate: new CryptKey(
                DM_ROOT.'/'.$validatedData->jwt_signing_key_private,
                $validatedData->jwt_signing_key_passphrase,
            ),
            jwtSigningKeyPublic: new CryptKey(
                DM_ROOT.'/'.$validatedData->jwt_signing_key_public,
                $validatedData->jwt_signing_key_passphrase,
            ),
            sessionKeyAuthorizationRequest: $validatedData->session_key_authorization_request,
            sessionKeyPkce: $validatedData->session_key_pkce,
            sessionKeyState: $validatedData->session_key_state,
        );
    }
}
