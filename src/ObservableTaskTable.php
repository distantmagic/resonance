<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Ds\Set;
use Swoole\Coroutine\Channel;
use Swoole\Table;

#[Singleton]
readonly class ObservableTaskTable
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

    public function __destruct()
    {
        $this->table->destroy();
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
            try {
                $this->table->set($slotId, [
                    'status' => $this->serializedPendingStatus,
                ]);

                foreach ($observableTask as $statusUpdate) {
                    $this->table->set($slotId, [
                        'status' => $this->serializer->serialize($statusUpdate),
                    ]);

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
            } finally {
                $this->availableRowsPool->freeAvailableRow($slotId);
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
