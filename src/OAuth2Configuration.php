<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Defuse\Crypto\Key;
use League\OAuth2\Server\CryptKey;

readonly class OAuth2Configuration
{
    public function __construct(
        public Key $encryptionKey,
        public CryptKey $jwtSigningKeyPrivate,
        public CryptKey $jwtSigningKeyPublic,
        public string $sessionKeyAuthorizationRequest,
        public string $sessionKeyPkce,
        public string $sessionKeyState,
    ) {}
}
