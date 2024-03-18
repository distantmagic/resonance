<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use Generator;
use Swoole\Table;

#[Singleton]
readonly class ObservableTaskTable
{
    private SwooleTableAvailableRowsPool $availableRowsPool;
    private string $serializedPendingStatus;
    private Table $table;

    public function __construct(
        ObservableTaskConfiguration $observableTaskConfiguration,
        private SerializerInterface $serializer,
    ) {
        $this->availableRowsPool = new SwooleTableAvailableRowsPool($observableTaskConfiguration->maxTasks);

        $this->serializedPendingStatus = $serializer->serialize(
            new ObservableTaskStatusUpdate(ObservableTaskStatus::Pending, null)
        );

        $this->table = new Table($observableTaskConfiguration->maxTasks);
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
     * @return Generator<non-empty-string,ObservableTaskStatusUpdate>
     */
    public function getStatuses(): Generator
    {
        /**
         * @var non-empty-string $taskId
         * @var mixed            $row explicitly mixed for typechecks
         */
        foreach ($this->table as $taskId => $row) {
            $status = $this->unserializeTableRow($row);

            if ($status instanceof ObservableTaskStatusUpdate) {
                yield $taskId => $status;
            }
        }
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
