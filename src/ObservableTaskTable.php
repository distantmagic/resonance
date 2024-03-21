<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Ds\Set;
use Generator;
use IteratorAggregate;
use RuntimeException;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use Swoole\Table;

/**
 * @template-implements IteratorAggregate<non-empty-string,?ObservableTaskStatusUpdate>
 */
#[Singleton]
readonly class ObservableTaskTable implements IteratorAggregate
{
    /**
     * @var Set<Channel>
     */
    public Set $observableChannels;

    private SwooleTableAvailableRowsPool $availableRowsPool;
    private string $serializedPendingStatus;
    private Table $table;

    public function __construct(
        ObservableTaskConfiguration $observableTaskConfiguration,
        private SerializerInterface $serializer,
    ) {
        $this->availableRowsPool = new SwooleTableAvailableRowsPool($observableTaskConfiguration->maxTasks);
        $this->observableChannels = new Set();
        $this->serializedPendingStatus = $serializer->serialize(
            new ObservableTaskStatusUpdate(ObservableTaskStatus::Pending, null)
        );

        $this->table = new Table(2 * $observableTaskConfiguration->maxTasks);
        $this->table->column('status', Table::TYPE_STRING, $observableTaskConfiguration->serializedStatusSize);
        $this->table->create();
    }

    /**
     * @return Generator<non-empty-string,?ObservableTaskStatusUpdate>
     */
    public function getIterator(): Generator
    {
        /**
         * @var non-empty-string $slotId
         * @var mixed            $row explicitly mixed for typechecks
         */
        foreach ($this->table as $slotId => $row) {
            yield $slotId => $this->unserializeTableRow($row);
        }
    }

    /**
     * @param non-empty-string $taskId
     */
    public function getStatus(string $taskId): ?ObservableTaskStatusUpdate
    {
        return $this->unserializeTableRow($this->table->get($taskId));
    }

    /**
     * @return non-empty-string
     */
    public function observe(ObservableTaskInterface $observableTask): string
    {
        $slotId = $this->availableRowsPool->nextAvailableRow();

        SwooleCoroutineHelper::mustGo(function () use ($slotId, $observableTask) {
            Coroutine::defer(function () use ($slotId) {
                $this->availableRowsPool->freeAvailableRow($slotId);
            });

            if (
                !$this->table->set($slotId, [
                    'status' => $this->serializedPendingStatus,
                ])
            ) {
                throw new RuntimeException('Unable to set an initial slot status');
            }

            foreach ($observableTask as $statusUpdate) {
                if (!$this->table->set($slotId, [
                        'status' => $this->serializer->serialize($statusUpdate),
                    ])
                ) {
                    throw new RuntimeException('Unable to update a slot status.');
                }

                if (!$this->observableChannels->isEmpty()) {
                    $slotStatusUpdate = new ObservableTaskSlotStatusUpdate($slotId, $statusUpdate);

                    foreach ($this->observableChannels as $observableChannel) {
                        $observableChannel->push($slotStatusUpdate);
                    }
                }

                if (ObservableTaskStatus::Running !== $statusUpdate->status) {
                    break;
                }
            }
        });

        return $slotId;
    }

    private function unserializeTableRow(mixed $row): ?ObservableTaskStatusUpdate
    {
        if (!is_array($row) || !is_string($row['status'])) {
            return null;
        }

        $unserialized = $this->serializer->unserialize($row['status']);

        if (!($unserialized instanceof ObservableTaskStatusUpdate)) {
            return null;
        }

        return $unserialized;
    }
}
