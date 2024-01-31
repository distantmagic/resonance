<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\MailerConfiguration;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Symfony\Component\Mime\Crypto\DkimSigner;

/**
 * @template-extends SingletonProvider<DkimSigner>
 */
#[Singleton(provides: DkimSigner::class)]
final readonly class DkimSignerProvider extends SingletonProvider
{
    public function __construct(
        private MailerConfiguration $mailerConfiguration,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): DkimSigner
    {
        return new DkimSigner(
            domainName: $this->mailerConfiguration->dkimDomainName,
            passphrase: $this->mailerConfiguration->dkimSigningKeyPassphrase,
            pk: 'file://'.$this->mailerConfiguration->dkimSigningKeyPrivate,
            selector: $this->mailerConfiguration->dkimSelector,
        );
    }
}
