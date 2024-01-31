<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use SensitiveParameter;

readonly class MailerConfiguration
{
    /**
     * @param non-empty-string $transportDsn
     */
    public function __construct(
        #[SensitiveParameter]
        public string $dkimDomainName,
        #[SensitiveParameter]
        public string $dkimSelector,
        #[SensitiveParameter]
        public string $dkimSigningKeyPassphrase,
        #[SensitiveParameter]
        public string $dkimSigningKeyPrivate,
        #[SensitiveParameter]
        public string $dkimSigningKeyPublic,
        #[SensitiveParameter]
        public string $transportDsn,
    ) {}
}
