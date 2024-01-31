<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\EventDispatcherInterface;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Distantmagic\Resonance\SwooleTaskServerMessageBus;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * @template-extends SingletonProvider<MailerInterface>
 */
#[Singleton(provides: MailerInterface::class)]
final readonly class MailerProvider extends SingletonProvider
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private TransportInterface $transport,
        private ?SwooleTaskServerMessageBus $swooleTaskServerMessageBus = null,
    ) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): MailerInterface
    {
        return new Mailer(
            bus: $this->swooleTaskServerMessageBus,
            dispatcher: $this->eventDispatcher,
            transport: $this->transport,
        );
    }
}
