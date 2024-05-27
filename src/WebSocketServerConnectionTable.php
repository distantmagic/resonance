<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\GrantsFeature;
use Distantmagic\Resonance\Attribute\RequiresBackendDriver;
use Distantmagic\Resonance\Attribute\Singleton;
use Generator;
use IteratorAggregate;
use RuntimeException;
use Swoole\Table;

/**
 * @template-implements IteratorAggregate<int,int>
 */
#[GrantsFeature(Feature::WebSocket)]
#[RequiresBackendDriver(BackendDriver::Swoole)]
#[Singleton]
readonly class WebSocketServerConnectionTable implements IteratorAggregate
{
    private Table $table;

    public function __construct(WebSocketConfiguration $webSocketConfiguration)
    {
        $this->table = new Table(2 * $webSocketConfiguration->maxConnections);
        $this->table->column('worker_id', Table::TYPE_INT);
        $this->table->create();
    }

    /**
     * @return Generator<int,int>
     */
    public function getIterator(): Generator
    {
        /**
         * @var string $fd
         * @var mixed  $row explicitly mixed for typechecks
         */
        foreach ($this->table as $fd => $row) {
            if (is_array($row) && array_key_exists('worker_id', $row)) {
                yield (int) $fd => (int) $row['worker_id'];
            } else {
                throw new RuntimeException('WebSocket table is corrupted');
            }
        }
    }

    public function registerConnection(int $fd, int $workerId): void
    {
        $this->table->set((string) $fd, [
            'worker_id' => $workerId,
        ]);
    }

    public function unregisterConnection(int $fd): void
    {
        $this->table->delete((string) $fd);
    }
}
