<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Serializer\Vanilla;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ObservableTaskTable::class)]
final class ObservableTaskTableTest extends TestCase
{
    use TestsDependencyInectionContainerTrait;

    private ?ObservableTaskConfiguration $observableTaskConfiguration = null;
    private ?ObservableTaskTable $observableTaskTable = null;

    protected function setUp(): void
    {
        $this->observableTaskConfiguration = new ObservableTaskConfiguration(
            maxTasks: 4,
            serializedStatusSize: 32768,
        );

        $this->observableTaskTable = new ObservableTaskTable(
            coroutineDriver: self::$container->coroutineDriver,
            observableTaskConfiguration: $this->observableTaskConfiguration,
            serializer: new Vanilla(),
        );
    }

    protected function tearDown(): void
    {
        self::$container->coroutineDriver->wait();
    }

    public function test_task_is_observed(): void
    {
        self::assertNotNull($this->observableTaskTable);
        self::assertNull($this->observableTaskTable->getStatus('0'));

        $slotId = $this->observableTaskTable->observe(new ObservableTask(static function () {
            yield new ObservableTaskStatusUpdate(
                ObservableTaskStatus::Running,
                'test',
            );
        }));

        self::assertSame('0', $slotId);

        $status = $this->observableTaskTable->getStatus($slotId);

        self::assertInstanceOf(ObservableTaskStatusUpdate::class, $status);
        self::assertSame(ObservableTaskStatus::Running, $status->status);
        self::assertSame('test', $status->data);
    }
}
