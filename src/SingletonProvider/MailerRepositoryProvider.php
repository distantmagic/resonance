<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\DependencyInjectionContainer;
use Distantmagic\Resonance\Feature;
use Distantmagic\Resonance\Mailer;
use Distantmagic\Resonance\MailerConfiguration;
use Distantmagic\Resonance\MailerRepository;
use Distantmagic\Resonance\MailerTransportConfiguration;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Distantmagic\Resonance\SwooleTaskServerMessageBus;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @template-extends SingletonProvider<MailerRepository>
 */
#[GrantsFeature(Feature::Mailer)]
#[Singleton(provides: MailerRepository::class)]
final readonly class MailerRepositoryProvider extends SingletonProvider
{
    public function __construct(
        private DependencyInjectionContainer $dependencyInjectionContainer,
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private MailerConfiguration $mailerConfiguration,
        private ?SwooleTaskServerMessageBus $swooleTaskServerMessageBus = null,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): MailerRepository
    {
        $mailerRepository = new MailerRepository();

        foreach ($this->mailerConfiguration->transportConfiguration as $name => $transportConfiguration) {
            $mailerRepository->mailer->put(
                $name,
                new Mailer(
                    dkimSigner: $this->buildDkimSigner($name, $transportConfiguration),
                    name: $name,
                    messageBus: $this->swooleTaskServerMessageBus,
                    transport: Transport::fromDsn(
                        client: $this->httpClient,
                        // dispatcher: $eventDispatcher,
                        dsn: $transportConfiguration->transportDsn,
                        logger: $this->logger,
                    ),
                )
            );
        }

        return $mailerRepository;
    }

    /**
     * @param non-empty-string $name
     */
    private function buildDkimSigner(string $name, MailerTransportConfiguration $transportConfiguration): ?DkimSigner
    {
        if (isset(
            $transportConfiguration->dkimDomainName,
            $transportConfiguration->dkimSigningKeyPassphrase,
            $transportConfiguration->dkimSigningKeyPrivate,
            $transportConfiguration->dkimSelector,
        )) {
            return new DkimSigner(
                domainName: $transportConfiguration->dkimDomainName,
                passphrase: $transportConfiguration->dkimSigningKeyPassphrase,
                pk: 'file://'.$transportConfiguration->dkimSigningKeyPrivate,
                selector: $transportConfiguration->dkimSelector,
            );
        }

        if (
            is_null($transportConfiguration->dkimDomainName)
            && is_null($transportConfiguration->dkimSigningKeyPassphrase)
            && is_null($transportConfiguration->dkimSigningKeyPrivate)
            && is_null($transportConfiguration->dkimSelector)
        ) {
            return null;
        }

        throw new RuntimeException(sprintf(
            <<<'ERROR_MESSAGE'
            If you want to use DKIM you need to fill all the DKIM settings.
            If you wan to disable DKIM for this transport, do not fill any DKIM
            settigns: "%s"
            ERROR_MESSAGE,
            $name,
        ));
    }
}
