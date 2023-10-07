<?php

declare(strict_types=1);

namespace Resonance\EventListener;

use Doctrine\SqlFormatter\SqlFormatter;
use Psr\Log\LoggerInterface;
use Resonance\Attribute\ListensTo;
use Resonance\Attribute\Singleton;
use Resonance\Event\SQLQueryBeforeExecute;
use Resonance\EventInterface;
use Resonance\EventListener;
use Resonance\SingletonCollection;

/**
 * @template-extends EventListener<SQLQueryBeforeExecute,void>
 */
#[ListensTo(SQLQueryBeforeExecute::class)]
#[Singleton(collection: SingletonCollection::EventListener)]
final readonly class SQLQueryLogger extends EventListener
{
    private SqlFormatter $sqlFormatter;

    public function __construct(private LoggerInterface $logger)
    {
        $this->sqlFormatter = new SqlFormatter();
    }

    /**
     * @param SQLQueryBeforeExecute $event
     */
    public function handle(EventInterface $event): void
    {
        $this->logger->debug($this->sqlFormatter->format($event->sql));
    }

    public function shouldRegister(): bool
    {
        return DM_DB_LOG_QUERIES;
    }
}
