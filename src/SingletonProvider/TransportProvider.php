<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\EventDispatcherInterface;
use Distantmagic\Resonance\MailerConfiguration;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @template-extends SingletonProvider<TransportInterface>
 */
#[Singleton(provides: TransportInterface::class)]
final readonly class TransportProvider extends SingletonProvider
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private MailerConfiguration $mailerConfiguration,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): TransportInterface
    {
        return Transport::fromDsn(
            client: $this->httpClient,
            dispatcher: $this->eventDispatcher,
            dsn: $this->mailerConfiguration->transportDsn,
            logger: $this->logger,
        );
    }
}
