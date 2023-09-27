<?php

declare(strict_types=1);

namespace Resonance\EventListener;

use Resonance\Attribute\ListensTo;
use Resonance\Attribute\Singleton;
use Resonance\Event\UserspaceThrowableNotCaptured;
use Resonance\EventInterface;
use Resonance\EventListener;
use Resonance\SingletonCollection;

use function Sentry\captureException;

/**
 * @template-extends EventListener<UserspaceThrowableNotCaptured>
 */
#[ListensTo(UserspaceThrowableNotCaptured::class)]
#[Singleton(collection: SingletonCollection::EventListener)]
final readonly class ReportThrowableToSentry extends EventListener
{
    /**
     * @param UserspaceThrowableNotCaptured $event
     */
    public function handle(EventInterface $event): void
    {
        captureException($event->throwable);
    }

    public function shouldRegister(): bool
    {
        return !empty(DM_SENTRY_DSN);
    }
}
