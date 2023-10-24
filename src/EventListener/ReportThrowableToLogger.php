<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\EventListener;

use Distantmagic\Resonance\ApplicationConfiguration;
use Distantmagic\Resonance\Attribute\ListensTo;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Environment;
use Distantmagic\Resonance\Event\UnhandledException;
use Distantmagic\Resonance\EventInterface;
use Distantmagic\Resonance\EventListener;
use Distantmagic\Resonance\SingletonCollection;
use Psr\Log\LoggerInterface;

/**
 * @template-extends EventListener<UnhandledException,void>
 */
#[ListensTo(UnhandledException::class)]
#[Singleton(collection: SingletonCollection::EventListener)]
final readonly class ReportThrowableToLogger extends EventListener
{
    public function __construct(
        private ApplicationConfiguration $app,
        private LoggerInterface $logger,
    ) {}

    /**
     * @param UnhandledException $event
     */
    public function handle(EventInterface $event): void
    {
        $this->logger->error((string) $event->throwable);
    }

    public function shouldRegister(): bool
    {
        return Environment::Development === $this->app->environment;
    }
}
