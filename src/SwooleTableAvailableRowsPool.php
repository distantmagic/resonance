<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use OverflowException;
use RuntimeException;
use Swoole\Atomic;
use Swoole\Table;
use UnderflowException;
use UnexpectedValueException;

readonly class SwooleTableAvailableRowsPool
{
    private Atomic $availableRowPointer;
    private Table $availableRows;

    public function __construct(private int $size)
    {
        $this->availableRowPointer = new Atomic(0);

        $this->availableRows = new Table(2 * $size);
        $this->availableRows->column('index', Table::TYPE_INT);

        if (!$this->availableRows->create()) {
            throw new RuntimeException('Unable to allocate table');
        }

        for ($i = 0; $i < $size; ++$i) {
            $this->availableRows->set((string) $i, [
                'index' => $i,
            ]);
        }
    }

    /**
     * @param non-empty-string $index
     */
    public function freeAvailableRow(string $index): void
    {
        $availableRowPointerValue = $this->availableRowPointer->sub(1);

        if ($availableRowPointerValue < 0) {
            throw new UnderflowException('No available rows');
        }

        if (
            !$this
                ->availableRows
                ->set((string) $availableRowPointerValue, [
                    'index' => (int) $index,
                ])
        ) {
            throw new RuntimeException('Unable to free available row');
        }
    }

    /**
     * @return non-empty-string
     */
    public function nextAvailableRow(): string
    {
        $availableRowPointerValue = ($this->availableRowPointer->add(1) - 1);

        if ($availableRowPointerValue >= $this->size) {
            throw new OverflowException('No available rows');
        }

        $row = $this
            ->availableRows
            ->get((string) $availableRowPointerValue)
        ;

        if (!is_array($row)) {
            throw new UnexpectedValueException('Corrupted row data.');
        }

        /**
         * @var non-empty-string
         */
        return (string) $row['index'];
    }
}
