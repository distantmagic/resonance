<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\EventListener;

use Distantmagic\Resonance\Attribute\ListensTo;
use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\Event\SQLQueryBeforeExecute;
use Distantmagic\Resonance\EventInterface;
use Distantmagic\Resonance\EventListener;
use Distantmagic\Resonance\SingletonCollection;
use Doctrine\SqlFormatter\SqlFormatter;
use Psr\Log\LoggerInterface;

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
