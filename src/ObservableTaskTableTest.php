<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Serializer\Vanilla;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Swoole\Event;

/**
 * @internal
 */
#[CoversClass(ObservableTaskTable::class)]
#[RunTestsInSeparateProcesses]
final class ObservableTaskTableTest extends TestCase
{
    private ?ObservableTaskConfiguration $observableTaskConfiguration = null;
    private ?ObservableTaskTable $observableTaskTable = null;

    protected function setUp(): void
    {
        $this->observableTaskConfiguration = new ObservableTaskConfiguration(
            maxTasks: 4,
            serializedStatusSize: 32768,
        );

        $this->observableTaskTable = new ObservableTaskTable(
            observableTaskConfiguration: $this->observableTaskConfiguration,
            serializer: new Vanilla(),
        );
    }

    protected function tearDown(): void
    {
        Event::wait();
    }

    public function test_channel_is_observed(): void
    {
        self::assertNotNull($this->observableTaskTable);

        $this->observableTaskTable->observers->add(static function (ObservableTaskSlotStatusUpdate $status): bool {
            return ObservableTaskStatus::Finished === $status->observableTaskStatusUpdate->status;
        });

        $this->observableTaskTable->observe(new ObservableTask(static function () {
            yield new ObservableTaskStatusUpdate(
                ObservableTaskStatus::Running,
                'test1',
            );

            yield new ObservableTaskStatusUpdate(
                ObservableTaskStatus::Finished,
                'test2',
            );
        }));
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
