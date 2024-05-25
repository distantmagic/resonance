<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use DateTimeImmutable;
use Distantmagic\Resonance\Attribute\Singleton;
use Generator;
use IteratorAggregate;
use RuntimeException;
use Swoole\Table;

/**
 * @template-implements IteratorAggregate<non-empty-string,ObservableTaskTableRow>
 */
#[Singleton]
readonly class ObservableTaskTable implements IteratorAggregate
{
    private SwooleTableAvailableRowsPool $availableRowsPool;
    private string $serializedPendingStatus;
    private Table $table;

    public function __construct(
        private CoroutineDriverInterface $coroutineDriver,
        ObservableTaskConfiguration $observableTaskConfiguration,
        private SerializerInterface $serializer,
    ) {
        $this->availableRowsPool = new SwooleTableAvailableRowsPool($observableTaskConfiguration->maxTasks);
        $this->serializedPendingStatus = $serializer->serialize(
            new ObservableTaskStatusUpdate(ObservableTaskStatus::Pending, null)
        );

        $this->table = new Table(2 * $observableTaskConfiguration->maxTasks);
        $this->table->column('category', Table::TYPE_STRING, 255);
        $this->table->column('modified_at', Table::TYPE_INT);
        $this->table->column('name', Table::TYPE_STRING, 255);
        $this->table->column('status', Table::TYPE_STRING, $observableTaskConfiguration->serializedStatusSize);
        $this->table->create();
    }

    /**
     * @return Generator<non-empty-string,ObservableTaskTableRow>
     */
    public function getIterator(): Generator
    {
        /**
         * @var non-empty-string $slotId
         * @var mixed            $row explicitly mixed for typechecks
         */
        foreach ($this->table as $slotId => $row) {
            $unserializedRow = $this->unserializeTableRow($row);

            if ($unserializedRow) {
                yield $slotId => $unserializedRow;
            }
        }
    }

    /**
     * @param non-empty-string $taskId
     */
    public function getStatus(string $taskId): ?ObservableTaskStatusUpdate
    {
        $row = $this->table->get($taskId);

        if (!is_array($row)) {
            return null;
        }

        return $this->unserializeTableStatusColumn($row);
    }

    /**
     * @return non-empty-string
     */
    public function observe(ObservableTaskInterface $observableTask): string
    {
        $slotId = $this->availableRowsPool->nextAvailableRow();

        $this->coroutineDriver->go(function () use ($slotId, $observableTask) {
            try {
                if (
                    !$this->table->set($slotId, [
                        'category' => $observableTask->getCategory(),
                        'modified_at' => time(),
                        'name' => $observableTask->getName(),
                        'status' => $this->serializedPendingStatus,
                    ])
                ) {
                    throw new RuntimeException('Unable to set an initial slot status');
                }

                foreach ($observableTask as $statusUpdate) {
                    if (
                        !$this->table->set($slotId, [
                            'category' => $observableTask->getCategory(),
                            'modified_at' => time(),
                            'name' => $observableTask->getName(),
                            'status' => $this->serializer->serialize($statusUpdate),
                        ])
                    ) {
                        throw new RuntimeException('Unable to update a slot status.');
                    }

                    if ($statusUpdate->status->isFinal()) {
                        break;
                    }
                }
            } finally {
                $this->availableRowsPool->freeAvailableRow($slotId);
            }
        });

        return $slotId;
    }

    private function unserializeTableRow(mixed $row): ?ObservableTaskTableRow
    {
        if (!is_array($row)) {
            return null;
        }

        $observableTaskStatusUpdate = $this->unserializeTableStatusColumn($row);

        if (is_null($observableTaskStatusUpdate)
            || !is_string($row['name'])
            || !is_string($row['category'])
            || !is_int($row['modified_at'])
        ) {
            return null;
        }

        return new ObservableTaskTableRow(
            name: $row['name'],
            category: $row['category'],
            modifiedAt: (new DateTimeImmutable())->setTimestamp($row['modified_at']),
            observableTaskStatusUpdate: $observableTaskStatusUpdate,
        );
    }

    private function unserializeTableStatusColumn(array $row): ?ObservableTaskStatusUpdate
    {
        if (!is_string($row['status'])) {
            return null;
        }

        $unserialized = $this->serializer->unserialize($row['status']);

        if (!($unserialized instanceof ObservableTaskStatusUpdate)) {
            return null;
        }

        return $unserialized;
    }
}
