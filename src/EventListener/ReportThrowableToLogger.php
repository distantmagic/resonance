<?php

declare(strict_types=1);

namespace Resonance\EventListener;

use Psr\Log\LoggerInterface;
use Resonance\Attribute\ListensTo;
use Resonance\Attribute\Singleton;
use Resonance\Environment;
use Resonance\Event\UnhandledException;
use Resonance\EventInterface;
use Resonance\EventListener;
use Resonance\SingletonCollection;

/**
 * @template-extends EventListener<UnhandledException,void>
 */
#[ListensTo(UnhandledException::class)]
#[Singleton(collection: SingletonCollection::EventListener)]
final readonly class ReportThrowableToLogger extends EventListener
{
    public function __construct(private LoggerInterface $logger) {}

    /**
     * @param UnhandledException $event
     */
    public function handle(EventInterface $event): void
    {
        $this->logger->error((string) $event->throwable);
    }

    public function shouldRegister(): bool
    {
        return DM_APP_ENV === Environment::Development;
    }
}
